<?php

namespace App\Livewire\Vendor;

use Livewire\Component;
use App\Models\Category as ModelsCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title(content: 'Category')]
class Category extends Component
{
    public $name, $description,$new_description,$new_name, $id;
    public function store(){
        DB::beginTransaction();
        try{
            $validation  = $this->validate([
                'name'=>'required|min:2|max:20|unique:categories,name',
                'description'=>'nullable|string',
            ]);

            $validation['vendor_id']= Auth::guard('vendor')->user()->id;
            $validation['name'] = ucwords(strtolower($validation['name']));
            ModelsCategory::create($validation);
            DB::commit();
            return redirect()->route('vendor.category')->with('success','Category Added Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('vendor.category')->with('error',$e->getMessage());
        }
    }

    public function edit($id){
        $this->reset();
        $category = ModelsCategory::find($id);
        $this->id = $category->id;
        $this->new_name = $category->name;
        $this->new_description = $category->description;
    }

    public function update(){
        DB::beginTransaction();
        try{
            $validation  = $this->validate([
                'new_name'=>'required|min:2|max:20|unique:categories,name,'.$this->id,
                'new_description'=>'nullable|string',
            ]);

            $category = ModelsCategory::find($this->id);
            $category->name = ucwords(strtolower($validation['new_name']));
            $category->description = $validation['new_description'];
            $category->save();
            DB::commit();
            return redirect()->route('vendor.category')->with('success','Category Updated Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('vendor.category')->with('error',$e->getMessage());
        }
    }

    public function delete($id){
        DB::beginTransaction();
        try{
            $category = ModelsCategory::find($id);
            $category->delete();
            DB::commit();
            return redirect()->route('vendor.category')->with('success','Category Deleted Successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('vendor.category')->with('error',$e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.vendor.category',[
            'categories'=>ModelsCategory::latest()->get(),
        ]);
    }
}
