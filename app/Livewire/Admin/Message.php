<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Message')]
class Message extends Component
{

    public function markAsUnread($id)
    {
        DB::beginTransaction();
        try {
            $message = ContactMessage::find($id);
            $message->update([
                'is_read' => false,
            ]);
            DB::commit();
            return redirect()->route('admin.message')->with('success', 'Marked as unread');
        } catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('admin.message')->with('error','Something went wrong');
        }
    }

     public function markAsRead($id)
    {
        DB::beginTransaction();
        try {
            $message = ContactMessage::find($id);
            $message->update([
                'is_read' => true,
            ]);
            DB::commit();
            return redirect()->route('admin.message')->with('success', 'Marked as read');
        } catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('admin.message')->with('error','Something went wrong');
        }
    }
    
    public function render()
    {
        return view('livewire.admin.message', [
            'messages' => ContactMessage::latest()->paginate(10),
        ]);
    }
}
