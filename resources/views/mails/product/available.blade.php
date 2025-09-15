@component('mail::message')
# Product Available

Hello,

We are excited to let you know that the product you were interested in is now available!

## Product Details

@if ($mainImage)
{!! $mainImage !!}  <!-- Use this if $mainImage contains raw HTML -->
@endif
{{-- {{$mainImage}} --}}

**Name:** {{ $product->title }}  

<p><strong>Description:</strong> {!! $product->description !!}</p>

@component('mail::button', ['url' => 'https://template-b27.pages.dev/product-details/' . $product->id])
View Product
@endcomponent

Thank you for shopping with us!

Regards,  
{{ $siteName }}
@endcomponent
