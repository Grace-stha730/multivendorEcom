<?php

namespace App\Livewire\Vendor;

use Livewire\Component;
use App\Models\Category as ModelsCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
#[Title(content: 'Category')]
class Category extends Component
{
    use \App\Livewire\Concerns\AuthorizesPermissions;
    use \App\Livewire\Concerns\PaginatesList;
    public $name, $description,$new_description,$new_name, $id;
    public function store(){
        $this->authorizeShop('category-manage');
        DB::beginTransaction();
        try{
            $validation  = $this->validate([
                'name'=>'required|min:2|max:20|unique:categories,name',
                'description'=>'nullable|string',
            ]);

            $validation['shop_id'] = Auth::guard('shop_user')->user()->shop_id;
            $validation['name'] = ucwords(strtolower($validation['name']));
            ModelsCategory::create($validation);
            DB::commit();
            return redirect()->route('shop-user.category')->with('success','Category added successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('shop-user.category')->with('error',$e->getMessage());
        }
    }

    public function edit($id){
        $this->authorizeShop('category-manage');
        $this->reset();
        $category = ModelsCategory::forCurrentShop()->findOrFail($id);
        $this->id = $category->id;
        $this->new_name = $category->name;
        $this->new_description = $category->description;
    }

    public function update(){
        $this->authorizeShop('category-manage');
        DB::beginTransaction();
        try{
            $validation  = $this->validate([
                'new_name'=>'required|min:2|max:20|unique:categories,name,'.$this->id,
                'new_description'=>'nullable|string',
            ]);

            $category = ModelsCategory::forCurrentShop()->findOrFail($this->id);
            $category->name = ucwords(strtolower($validation['new_name']));
            $category->description = $validation['new_description'];
            $category->save();
            DB::commit();
            return redirect()->route('shop-user.category')->with('success','Category updated successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('shop-user.category')->with('error',$e->getMessage());
        }
    }

    public function delete($id){
        $this->authorizeShop('category-manage');
        DB::beginTransaction();
        try{
            $category = ModelsCategory::forCurrentShop()->findOrFail($id);
            $category->delete();
            DB::commit();
            return redirect()->route('shop-user.category')->with('success','Category deleted successfully');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('shop-user.category')->with('error',$e->getMessage());
        }
    }
    public function render()
    {
        return view('livewire.vendor.category',[
            'categories'=>ModelsCategory::latest()->paginate(15),
        ]);
    }
}
