<?php

namespace App\Http\Validators;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OfficeValidator
{
    public static function validate(Request $request, ?int $officeId = null)
    {
        $sometimes = Rule::when(($officeId !== null), 'sometimes');
        $attributes = $request->validate(
            [
                'title' => [$sometimes, 'required', 'string'],
                'description' => [$sometimes, 'required', 'string'],
                'lat' => [$sometimes, 'required', 'numeric'],
                'lng' => [$sometimes, 'required', 'numeric'],
                'address_line1' => [$sometimes, 'required', 'string'],
                'price_per_day' => [$sometimes, 'required', 'integer', 'min:100'],
                'featured_image_id' => [Rule::exists('images', 'id')->where('resource_type', 'office')->where('resource_id', $officeId)],
                'hidden' => ['bool'],
                'monthly_discount' => ['integer', 'min:0'],

                'tags' => ['array'],
                'tags.*' => ['integer', Rule::exists('tags', 'id')],
            ]
        );
        $attributes['user_id'] = auth()->id();

        return $attributes;
    }
}
