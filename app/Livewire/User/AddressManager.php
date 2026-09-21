<?php

namespace App\Livewire\User;

use App\Models\District;
use App\Models\Province;
use App\Models\UserAddress;
use App\Rules\PhoneNumber;
use App\Services\UserAddressService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Mary\Traits\Toast;

/**
 * The one address component. mode="select" is used on checkout (cards you pick from), mode="manage" on the
 * Settings page (edit, delete, set default). Both use the same add/edit form.
 */
class AddressManager extends Component
{
    use Toast;

    #[Locked]
    public string $mode = 'manage';

    /** In select mode: the chosen address id, bound to the parent with wire:model. */
    #[Modelable]
    public $selectedId = null;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $receiver_name = '', $contact = '', $city = '', $tole = '', $address_type = 'home';
    public $province_id = null, $district_id = null;
    public ?string $office_start_time = null, $office_end_time = null;
    public bool $make_default = false;

    public bool $deleteModal = false;
    public ?int $deleteId = null;

    public function mount(string $mode = 'manage'): void
    {
        $this->mode = in_array($mode, ['select', 'manage'], true) ? $mode : 'manage';
    }

    protected function rules(): array
    {
        return [
            'receiver_name' => 'required|string|max:120',
            'contact' => ['required', new PhoneNumber()],
            'province_id' => 'required|exists:provinces,id',
            'district_id' => ['required', Rule::exists('districts', 'id')->where('province_id', $this->province_id)],
            'city' => 'required|string|max:120',
            'tole' => 'required|string|max:120',
            'address_type' => 'required|in:home,office',
            'office_start_time' => 'required_if:address_type,office|nullable|date_format:H:i',
            'office_end_time' => 'required_if:address_type,office|nullable|date_format:H:i|after:office_start_time',
        ];
    }

    protected function messages(): array
    {
        return [
            'province_id.required' => 'Please select a province.',
            'district_id.required' => 'Please select a district.',
            'office_start_time.required_if' => 'Enter the time the office opens.',
            'office_end_time.required_if' => 'Enter the time the office closes.',
            'office_end_time.after' => 'The closing time must be after the opening time.',
        ];
    }

    public function updatedProvinceId(): void
    {
        $this->district_id = null;
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->receiver_name = (string) Auth::guard('web')->user()?->name;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $address = $this->owned($id);
        $this->resetForm();
        $this->editingId = $address->id;
        $this->receiver_name = $address->receiver_name;
        $this->contact = $address->contact;
        $this->province_id = $address->province_id;
        $this->district_id = $address->district_id;
        $this->city = $address->city;
        $this->tole = $address->tole;
        $this->address_type = $address->address_type;
        $this->office_start_time = $address->timeForInput($address->office_start_time);
        $this->office_end_time = $address->timeForInput($address->office_end_time);
        $this->make_default = $address->is_default;
        $this->showForm = true;
    }

    public function save(UserAddressService $service): void
    {
        $data = $this->validate();
        $user = Auth::guard('web')->user();

        if ($this->editingId) {
            $address = $service->update($this->owned($this->editingId), $data, $this->make_default);
            $this->success('Address updated', 'Your changes were saved.', 'toast-bottom');
        } else {
            $address = $service->create($user, $data, $this->make_default);
            $this->success('Address added', 'Your new address is ready to use.', 'toast-bottom');
        }

        // On checkout a newly added (or just completed) address becomes the selected one right away.
        if ($this->mode === 'select' && (!$this->editingId || (int) $this->selectedId === $address->id || $this->selectedId === null)) {
            $this->selectedId = $address->id;
        }

        $this->closeForm();
        $this->dispatch('address-saved');
    }

    /** Checkout: pick an address. Incomplete (migrated) ones must be completed first. */
    public function select(int $id): void
    {
        $address = $this->owned($id);

        if (!$address->isComplete()) {
            $this->openEdit($id);
            $this->error('Complete this address', 'Please choose the province and district for this address first.', 'toast-bottom');

            return;
        }

        $this->selectedId = $address->id;
    }

    public function makeDefault(int $id, UserAddressService $service): void
    {
        $service->makeDefault($this->owned($id));
        $this->success('Default address changed', null, 'toast-bottom');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $this->owned($id)->id;
        $this->deleteModal = true;
    }

    public function deleteAddress(UserAddressService $service): void
    {
        $address = $this->owned($this->deleteId);
        $error = $service->delete($address);

        $this->deleteModal = false;
        $this->deleteId = null;

        if ($error) {
            $this->error('Cannot delete', $error, 'toast-bottom', timeout: 8000);

            return;
        }

        if ((int) $this->selectedId === $address->id) {
            $this->selectedId = $service->preferred(Auth::guard('web')->user())?->id;
        }

        $this->success('Address deleted', null, 'toast-bottom');
        $this->dispatch('address-saved');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'receiver_name', 'contact', 'city', 'tole', 'province_id', 'district_id', 'office_start_time', 'office_end_time', 'make_default']);
        $this->address_type = 'home';
        $this->resetValidation();
    }

    /** Only ever the logged-in customer's own address. */
    private function owned(?int $id): UserAddress
    {
        return UserAddress::where('user_id', Auth::guard('web')->id())->findOrFail($id);
    }

    public function render()
    {
        $userId = Auth::guard('web')->id();

        return view('livewire.user.address-manager', [
            'addresses' => UserAddress::where('user_id', $userId)->with(['province', 'district'])
                ->orderByRaw("address_category = 'real' DESC")->orderByDesc('is_default')->orderBy('id')->get(),
            'provinces' => Province::orderBy('name')->get(),
            'districts' => $this->province_id ? District::where('province_id', $this->province_id)->orderBy('name')->get() : collect(),
        ]);
    }
}
