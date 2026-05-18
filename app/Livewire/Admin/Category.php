<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Category as modelCategory;

#[Layout('components.layouts.admin')]
#[Title('Category')]
class Category extends Component
{
    public $name, $description;
    public $categoryId, $new_name, $new_description;
    public function store()
    {
        $validation = $this->validate([
            'name' => 'required|min:2|max:20|unique:categories,name',
            'description' => 'nullable|string',
        ]);
        DB::beginTransaction();
        try {
            $validation['admin_id'] = Auth::guard('admin')->user()->id;
            $validation['name'] = ucwords(strtolower($validation['name']));
            $category = modelCategory::create($validation);
            if ($category) {
                DB::commit();
                return redirect()->route('admin.category')->with('success', 'Category Created Successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.category')->with('error', 'Something went wrong' . $e->getMessage());
        }

    }

    public function edit($id)
    {
        $category = modelCategory::find($id);
        $this->categoryId = $category->id;
        $this->new_name = $category->name;
        $this->new_description = $category->description;
    }

    public function update()
    {
        $validation = $this->validate([
            'new_name' => 'required|min:2|max:20|unique:categories,name,' . $this->categoryId,
            'new_description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $category = modelCategory::find($this->categoryId);
            if ($category) {
                $category->name = ucwords(strtolower($validation['new_name']));
                $category->description = $validation['new_description'];
                $category->save();
                DB::commit();
                return redirect()->route('admin.category')->with('success', 'category update successfully');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.category')->with('error', 'Something went wrong' . $e->getMessage());
        }
    }

    public function delete($id){
        DB::beginTransaction();
        try{
            $category = modelCategory::find($id);
            if($category){
                $category->delete();
                DB::commit();
                return redirect()->route('admin.category')->with('success', 'Category Delete Successfully');
            }
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('admin.category')->with('error', 'Something went wrong'.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.category', [
            'categories' => modelCategory::with('vendor', 'admin')->get(),
        ]);
    }
}
