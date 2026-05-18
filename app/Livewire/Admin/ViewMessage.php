<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Message Detail')]
class ViewMessage extends Component
{
    public $message;
    public function mount($id)
    {
        $this->message = ContactMessage::find($id);
    }

    public function markAsUnread()
    {
        DB::beginTransaction();
        try {
            $message = ContactMessage::find($this->message->id);
            $message->update([
                'is_read' => false,
            ]);
            DB::commit();
            return redirect()->route('admin.message-datail', ['id' => $this->message->id])->with('success', 'Marked as unread');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.message-datail', ['id' => $this->message->id])->with('error', 'Something went wrong');
        }
    }

    public function markAsRead()
    {
        DB::beginTransaction();
        try {
            $message = ContactMessage::find($this->message->id);
            $message->update([
                'is_read' => true,
            ]);
            DB::commit();
            return redirect()->route('admin.message-datail', ['id' => $this->message->id])->with('success', 'Marked as read');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.message-datail', ['id' => $this->message->id])->with('error', 'Something went wrong');
        }
    }

    public function render()
    {
        return view('livewire.admin.view-message', [
            'message' => $this->message,
        ]);
    }
}
