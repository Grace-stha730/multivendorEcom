<?php

namespace App\Livewire\User;

use App\Models\ContactMessage;
use Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title(content: 'Contact Us')]
#[Layout('components.layouts.user')]
class ContactUs extends Component
{
    public $contact, $name, $email, $subject, $message;
    public function mount()
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            // $this->contact = ContactMessage::find(Auth::guard('web')->user()->id);
            $this->name = $user->name;
            $this->email = $user->email;
        }
    }

    public function messageSubmit()
    {
        $validation = $this->validate([
            'name' => 'required|min:3|max:25',
            'email' => 'required|email|',
            'subject' => 'required|min:3|max:30',
            'message' => 'required|min:3',
        ]);
        DB::beginTransaction();
        $user = Auth::guard('web')->user();
        try {
            if ($user) {
                $validation['user_id'] = $user->id;
            }
            ContactMessage::create($validation);
            DB::commit();
            return redirect()->route('user.contact-us')->with('success','Thanks for reaching out! We’ve received your inquiry and will respond soon.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('user.contact-us')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.user.contact-us');
    }
}
