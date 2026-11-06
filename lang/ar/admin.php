<?php

return [
    'locales' => [
        'ar' => 'عربي',
        'en' => 'English',
    ],

    'navigation' => [
        'catalog' => 'الكتالوج',
        'content' => 'المحتوى',
        'sales' => 'المبيعات',
        'seo' => 'SEO',
        'system' => 'النظام',
    ],

    'publication' => [
        'blocked' => 'لا يمكن النشر قبل استكمال البيانات التالية',
        'blockers' => [
            'name' => 'الاسم باللغتين',
            'short_description' => 'الوصف المختصر باللغتين',
            'main_image' => 'الصورة الرئيسية',
            'category_unpublished' => 'التصنيف الأساسي غير منشور',
        ],
    ],

    'common' => [
        'sort' => 'الترتيب',
        'slug' => 'الرابط المختصر',
        'slug_help' => 'أحرف إنجليزية صغيرة وأرقام وشرطات فقط. تغييره بعد النشر يتطلب تحويلًا 301.',
        'review_status' => 'حالة المراجعة',
        'updated_at' => 'آخر تعديل',
        'published_at' => 'تاريخ النشر',
        'seo' => 'SEO',
        'seo_title' => 'عنوان البحث',
        'seo_description' => 'الوصف التعريفي',
        'seo_noindex' => 'منع الفهرسة',
        'seo_help' => 'اتركه فارغًا لاستخدام العنوان والوصف المختصر تلقائيًا.',
        'images' => 'الصور',
        'alt_text' => 'النص البديل',
    ],

    'products' => [
        'label' => 'منتج',
        'plural' => 'المنتجات',
        'tabs' => [
            'general' => 'عام',
            'content' => 'الوصف',
            'specs' => 'المواصفات',
            'availability' => 'التوفر والسعر',
            'media' => 'الصور والملفات',
            'review' => 'المراجعة والمصدر',
        ],
        'fields' => [
            'name' => 'الاسم',
            'appendix_no' => 'رقم الملحق',
            'sku' => 'SKU',
            'sku_help' => 'رمز داخلي، لا يُعرض كرقم مصنّع.',
            'primary_category' => 'التصنيف الأساسي',
            'categories' => 'تصنيفات إضافية',
            'is_featured' => 'منتج مختار للرئيسية',
            'short_description' => 'الوصف المختصر',
            'description' => 'الوصف التفصيلي',
            'uses' => 'الاستخدامات العامة',
            'material' => 'المادة',
            'grade' => 'درجة الصلب',
            'standard' => 'المعيار',
            'standard_help' => 'مثل G90 أو Sch40، فقط عند توثيقه.',
            'origin' => 'المنشأ',
            'origin_help' => 'فقط إذا كان مؤكدًا.',
            'brand' => 'العلامة',
            'model' => 'الموديل',
            'sale_unit' => 'وحدة البيع',
            'condition' => 'حالة المنتج',
            'spec_values' => 'مواصفات إضافية',
            'spec_attribute' => 'الخاصية',
            'value' => 'القيمة',
            'value_numeric' => 'القيمة الرقمية (للفلاتر)',
            'availability' => 'التوفر',
            'availability_updated_at' => 'آخر تحديث للتوفر',
            'price' => 'السعر',
            'price_help' => 'اختياري. اتركه فارغًا إذا لم يكن معتمدًا، ولا تضع قيمة تقديرية.',
            'price_currency' => 'العملة',
            'main_image' => 'الصورة الرئيسية',
            'gallery' => 'معرض الصور',
            'documents' => 'الكتالوجات والشهادات (PDF)',
            'related' => 'منتجات مرتبطة',
            'missing_fields' => 'النواقص',
            'missing_fields_help' => 'ما يلزم تأكيده مع العميل قبل النشر.',
            'source_reference' => 'مصدر البيانات',
            'source_reviewed_at' => 'تاريخ مراجعة المصدر',
            'historical_specs' => 'مواصفات تاريخية (مرجع فقط، لا تُعرض كتوفر)',
        ],
        'filters' => [
            'missing_main_image' => 'بدون صورة رئيسية',
            'has_missing_fields' => 'لديه نواقص',
        ],
        'actions' => [
            'update_availability' => 'تحديث التوفر',
            'change_review_status' => 'تغيير حالة المراجعة',
            'bulk_publish_partial' => 'نُشر :published منتج، وتعذّر نشر :blocked بسبب نواقص.',
        ],
    ],

    'variants' => [
        'label' => 'متغير',
        'plural' => 'المتغيرات',
        'fields' => [
            'label' => 'الوصف',
            'thickness_mm' => 'السماكة (ملم)',
            'width_mm' => 'العرض (ملم)',
            'length_mm' => 'الطول (ملم)',
            'diameter_mm' => 'القطر (ملم)',
            'nominal_size' => 'المقاس الاسمي',
            'nominal_size_help' => 'مثل 2" للمواسير. لا تخلط الإنش بالملم دون توضيح.',
            'inherit_availability' => 'نفس توفر المنتج',
        ],
    ],

    'media' => [
        'label' => 'صورة',
        'plural' => 'الصور والنصوص البديلة',
        'collection' => 'المجموعة',
        'file' => 'الملف',
        'missing_alt' => 'بدون نص بديل',
    ],

    'categories' => [
        'label' => 'تصنيف',
        'plural' => 'التصنيفات',
        'fields' => [
            'name' => 'الاسم',
            'description' => 'الوصف',
            'description_help' => 'وصف أصلي مختصر يظهر أعلى صفحة التصنيف.',
            'parent' => 'التصنيف الأب',
            'cover' => 'صورة التصنيف',
            'products_count' => 'عدد المنتجات',
        ],
    ],

    'sale_units' => [
        'label' => 'وحدة بيع',
        'plural' => 'وحدات البيع',
        'fields' => [
            'key' => 'المعرّف',
            'key_help' => 'معرّف ثابت بالإنجليزية مثل piece أو ton.',
            'name' => 'الاسم',
        ],
    ],

    'spec_attributes' => [
        'label' => 'خاصية',
        'plural' => 'خصائص المواصفات',
        'fields' => [
            'key' => 'المعرّف',
            'name' => 'الاسم',
            'unit' => 'الوحدة',
            'unit_help' => 'مثل mm أو kW أو bar. لا تخلط وحدتين في خاصية واحدة.',
            'type' => 'النوع',
            'is_filterable' => 'تظهر كفلتر في الموقع',
            'is_filterable_help' => 'يظهر الفلتر فقط في التصنيفات التي تملك منتجاتها قيمة لهذه الخاصية.',
        ],
        'types' => [
            'text' => 'نص',
            'number' => 'رقم',
        ],
    ],

    'synonyms' => [
        'label' => 'مجموعة مرادفات',
        'plural' => 'مرادفات البحث',
        'fields' => [
            'terms' => 'الكلمات',
            'terms_help' => 'كل الكلمات في المجموعة تعتبر متطابقة في البحث، بالعربية والإنجليزية. اضغط Enter بعد كل كلمة.',
        ],
    ],

    'in_use' => 'لا يمكن الحذف لأنه مستخدم في :count سجل.',
];
