<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRequest;
use App\Models\MainCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VendorCreated;
use DB;
use Illuminate\Support\Str;

class VendorsController extends Controller
{


    public function index()
    {
        $vendors = Vendor::selection()->paginate(PAGINATION_COUNT);
        return view('admin.vendors.index', compact('vendors'));
    }

    public function create()
    {
        $categories = MainCategory::defaultlang()->active()->get();
        return view('admin.vendors.create', compact('categories'));
    }

    public function store(VendorRequest $request)
    {
//        return $request;
        try {

            if (!$request->has('active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);

//            $filePath = "";
//            if ($request->has('logo')) {
//                $filePath = uploadImage('vendors', $request->logo);
//            }
            $imagePath = "";
            if($request->hasFile('logo')){
                // update img
                $imagePath = parent::uploadImage($request->file('logo'),'images/vendors');
                $request['logo'] = $imagePath ;
            }

            $vendor = Vendor::create([
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'active' => $request->active,
                'address' => $request->address,
                'logo' => $imagePath,
                'password' => $request->password, //is bcrypt in folder App\Models\Vendor.php (use mutators)
                'category_id' => $request->category_id,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

                //  to send Notification in mail use folder {App\Notifications\VendorCreated.php}
            Notification::send($vendor, new VendorCreated($vendor));

            return redirect()->route('admin.vendors')->with(['success' => 'تم الحفظ بنجاح']);

        } catch (\Exception $ex) {
//            return $ex;
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);

        }
    }

    public function edit($id)
    {
        try {

            $vendor = Vendor::Selection()->find($id);
            if (!$vendor)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر غير موجود او ربما يكون محذوفا ']);

            $categories = MainCategory::where('translation_of', 0)->active()->get();

            return view('admin.vendors.edit', compact('vendor', 'categories'));

        } catch (\Exception $exception) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function update($id, VendorRequest $request)
    {

        try {

            $vendor = Vendor::Selection()->find($id);
            if (!$vendor)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا المتجر غير موجود او ربما يكون محذوفا ']);


            DB::beginTransaction();
            //photo
//            if ($request->has('logo') ) {
//                 $filePath = uploadImage('vendors', $request->logo);
//                Vendor::where('id', $id)
//                    ->update([
//                        'logo' => $filePath,
//                    ]);
//            }
            if($request->hasFile('logo')){
                // update img
                $imagePath = parent::uploadImage($request->file('logo'),'images/vendors');
                Vendor::where('id', $id)
                    ->update([
                        'logo' => $imagePath,
                    ]);
            }


            if (!$request->has('active'))
                $request->request->add(['active' => 0]);
            else
                $request->request->add(['active' => 1]);

             $data = $request->except('_token', 'id', 'logo', 'password');


            if ($request->has('password') && !is_null($request->  password)) {
                $data['password'] = $request->password;//is bcrypt in folder App\Models\Vendor.php (use mutators)
            }

            Vendor::where('id', $id)
                ->update(
                    $data
                );

            DB::commit();
            return redirect()->route('admin.vendors')->with(['success' => 'تم التحديث بنجاح']);
        } catch (\Exception $exception) {
//            return $exception;
            DB::rollback();
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }

    }

    public function destroy($id){
        try {
            $vendors = Vendor::find($id);
            if (!$vendors)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا القسم غير موجود ']);
           ######  لا استطيع حذف هذا المتجر اذا كان له أقسام فرعية (تحتاج إلى تعديل) بعد اضافة sub_category  #######
//            $subCategory = $vendors->subCategory();
//            if (isset($subCategory) && $subCategory->count() > 0) {
//                return redirect()->route('admin.vendors')->with(['error' => 'لأ يمكن حذف هذا القسم  ']);
//            }

            $image = Str::after($vendors->logo, 'assets/');
            $image = base_path('assets/' . $image);
            unlink($image); //delete from folder


            $vendors->delete();
            return redirect()->route('admin.vendors')->with(['success' => 'تم حذف المتجر بنجاح']);

        } catch (\Exception $ex) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }

    public function changeStatus($id){
        try {

            $vendor = Vendor::find($id);
            if (!$vendor)
                return redirect()->route('admin.vendors')->with(['error' => 'هذا القسم غير موجود ']);
################### active if main category is active if not you don't active it ##########################3
            if ($vendor->category-> active  == 1){
                $status =  $vendor -> active  == 0 ? 1 : 0;
                $vendor -> update(['active' =>$status ]);
                return redirect()->route('admin.vendors')->with(['success' => ' تم تغيير الحالة بنجاح ']);
            }else{
                return redirect()->route('admin.vendors')->with(['error' => 'لا يمكن تغير الحالة ,القسم الرئيسي غير مفعل']);
            }


        } catch (\Exception $ex) {
            return redirect()->route('admin.vendors')->with(['error' => 'حدث خطا ما برجاء المحاوله لاحقا']);
        }
    }


}
