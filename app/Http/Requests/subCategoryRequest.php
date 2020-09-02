<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class  subCategoryRequest extends FormRequest{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'photo' => 'required_without:id|mimes:jpg,jpeg,png',
            'category' => 'required|array|min:1',
            'category.*.name' => 'required',
            'category.*.abbr' => 'required',
            'category_id'  => 'required|exists:main_categories,id',
            'parent_id'  => 'required',

        ];
    }


    public function messages(){

        return [
            'required'  => 'هذا الحقل مطلوب ',
            'category_id.exists'  => 'القسم غير موجود ',
            'photo.required_without'  => 'الصوره مطلوبة',
        ];
    }

}
