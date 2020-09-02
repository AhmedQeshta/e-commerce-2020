<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\subCategoryRequest;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use DB;


class SubCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    public function index()
    {
        $default_lang = get_default_lang();
        $subCategories = SubCategory::where('translation_lang', $default_lang)
            ->selection()
            ->get();

        return view('admin.subcategories.index', compact('subCategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $categories = MainCategory::defaultlang()->active()->get();
        $SuperSubCategories = SubCategory::defaultlang()->active()->get();
        return view('admin.subcategories.create',compact('categories','SuperSubCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param subCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(subCategoryRequest $request){

        try {


            $sub_categories = collect($request->category);
            $filter = $sub_categories->filter(function ($value, $key) {
                return $value['abbr'] == get_default_lang();
            });



            if (!$request->has('category.0.active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);


            $imagePath = "";
            if($request->hasFile('photo')){
                // update img
                $imagePath = parent::uploadImage($request->file('photo'),'images/subCategory');
                $request['photo'] = $imagePath ;
            }

//            default lang *(ar)
            $default_sub_category = array_values($filter->all()) [0];
            $default_sub_category_id = SubCategory::insertGetId([
                'translation_lang' => $default_sub_category['abbr'],
                'translation_of' => 0,
                'name' => $default_sub_category['name'],
                'slug' => str::slug($default_sub_category['name']),
                'active' => $request->active,
                'category_id' => $request->category_id,
                'parent_id' => $request->parent_id,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'photo' => $imagePath
            ]);

            DB::beginTransaction();

            $subCategories = $sub_categories->filter(function ($value, $key) {
                return $value['abbr'] != get_default_lang();
            });

            if (isset($subCategories) && $subCategories->count()) {
                $categories_arr = [];
                foreach ($subCategories as $subCategory) {
                    $categories_arr[] = [
                        'translation_lang' => $subCategory['abbr'],
                        'translation_of' => $default_sub_category_id,
                        'name' => $subCategory['name'],
                        'slug' => str::slug($subCategory['name']),
                        'active' => $request->active,
                        'category_id' => $request->category_id,
                        'parent_id' => $request->parent_id,
                        'created_at' => $request->created_at,
                        'updated_at' => $request->updated_at,
                        'photo' => $imagePath
                    ];
                }

                SubCategory::insert($categories_arr);
            }
            DB::commit();

            return redirect()->route('admin.subcategories')->with(['success' => 'تم الحفظ بنجاح']);

        } catch (\Exception $ex) {

            DB::rollback();
            return  $ex;
            return redirect()->route('admin.subcategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $subCategory_id
     * @return Application|Factory|RedirectResponse|View
     */
    public function edit($subCategory_id)
    {
        //get specific categories and its translations
        $subCategory = SubCategory::with('langCategories')
            ->selection()
            ->find($subCategory_id);
        $categories = MainCategory::where('translation_of', 0)->active()->get();
        $SuperSubCategories = SubCategory::defaultlang()->active()->get();
        if (!$subCategory)
            return redirect()->route('admin.subcategories')->with(['error' => 'هذا القسم غير موجود ']);

        return view('admin.subcategories.edit', compact('subCategory','categories','SuperSubCategories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param subCategoryRequest $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(subCategoryRequest $request, $id){

        try {
            $sub_category = SubCategory::find($id);

            if (!$sub_category)
                return redirect()->route('admin.subcategories')->with(['error' => 'هذا القسم غير موجود ']);

            // update date

            $category = array_values($request->category) [0];

            ########## to active or not to sub sub_category #########
            $subCategories = SubCategory::defaultlang()->get();
            foreach ($subCategories as $sub_Category){
                if ($id == $sub_Category->parent_id ){
                    $sub_Category-> update(['active' => $sub_category -> active  == 0 ? 1 : 0 ]);
                }
            }


            if (!$request->has('category.0.active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);


            // save image

            if($request->hasFile('photo')){
                // update img
                $imagePath = parent::uploadImage($request->file('photo'),'images/subCategory');
                SubCategory::where('id', $id)
                    ->update([
                        'photo' => $imagePath,
                    ]);
            }


            SubCategory::where('id', $id)
                ->update([
                    'name' => $category['name'],
                    'parent_id' => $request->parent_id,
                    'category_id' => $request->category_id,
                    'slug' => str::slug($category['name']),
                    'active' => $request->active,
                ]);

            return redirect()->route('admin.subcategories')->with(['success' => 'تم ألتحديث بنجاح']);

        } catch (\Exception $ex) {
            return redirect()->route('admin.subcategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {

        try {
            $sub_category = SubCategory::find($id);
            if (!$sub_category)
                return redirect()->route('admin.subcategories')->with(['error' => 'هذا القسم غير موجود ']);

      ###### if have sub sub_category in relation dont Remove ##########
            $subCategories = SubCategory::defaultlang()->get();
            foreach ($subCategories as $subCategory){
                if ($id == $subCategory->parent_id ){
                    return redirect()->route('admin.subcategories')->with(['error' => 'لا يكمن حذف هذا القسم , يوجد قسم فرعي تابع له  ']);
                }
            }


            $image = Str::after($sub_category->photo, 'assets/');
            $image = base_path('assets/' . $image);
            unlink($image); //delete from folder


            $sub_category->langCategories()->delete();// delete category translation with default lang(ar)
            $sub_category->delete();
            return redirect()->route('admin.subcategories')->with(['success' => 'تم حذف القسم بنجاح']);

        } catch (\Exception $ex) {
            return redirect()->route('admin.subcategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function changeStatus($id){

        try {
            $subCategory = SubCategory::find($id);
            if (!$subCategory)
                return redirect()->route('admin.subcategories')->with(['error' => 'هذا القسم غير موجود ']);


            $status =  $subCategory -> active  == 0 ? 1 : 0;

            ########## to active or not to sub sub_category #########
            $subCategories = SubCategory::defaultlang()->get();
            foreach ($subCategories as $sub_Category){
                if ($id == $sub_Category->parent_id ){
                     $sub_Category-> update(['active' => $subCategory -> active  == 0 ? 1 : 0 ]);
                }
            }

            $subCategory -> update(['active' =>$status ]);

            return redirect()->route('admin.subcategories')->with(['success' => ' تم تغيير الحالة بنجاح ']);

        } catch (\Exception $ex) {
            return redirect()->route('admin.subcategories')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

}
