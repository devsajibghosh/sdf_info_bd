<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (Bangla)
    |--------------------------------------------------------------------------
    |
    | Bangla translation of lang/en/validation.php. Keep both files in sync:
    | every key added to the English file should get a Bangla line here too,
    | otherwise Laravel silently falls back to the English text for Bangla
    | users (app.fallback_locale).
    |
    */

    'accepted' => ':attribute অবশ্যই গ্রহণ করতে হবে।',
    'accepted_if' => ':other :value হলে :attribute অবশ্যই গ্রহণ করতে হবে।',
    'active_url' => ':attribute অবশ্যই একটি বৈধ URL হতে হবে।',
    'after' => ':attribute অবশ্যই :date তারিখের পরে হতে হবে।',
    'after_or_equal' => ':attribute অবশ্যই :date তারিখের সমান বা পরে হতে হবে।',
    'alpha' => ':attribute শুধুমাত্র অক্ষর ধারণ করতে পারবে।',
    'alpha_dash' => ':attribute শুধুমাত্র অক্ষর, সংখ্যা, ড্যাশ এবং আন্ডারস্কোর ধারণ করতে পারবে।',
    'alpha_num' => ':attribute শুধুমাত্র অক্ষর এবং সংখ্যা ধারণ করতে পারবে।',
    'any_of' => ':attribute সঠিক নয়।',
    'array' => ':attribute অবশ্যই একটি অ্যারে হতে হবে।',
    'ascii' => ':attribute শুধুমাত্র সিঙ্গেল-বাইট অক্ষর এবং চিহ্ন ধারণ করতে পারবে।',
    'before' => ':attribute অবশ্যই :date তারিখের আগে হতে হবে।',
    'before_or_equal' => ':attribute অবশ্যই :date তারিখের সমান বা আগে হতে হবে।',
    'between' => [
        'array' => ':attribute অবশ্যই :min থেকে :max আইটেমের মধ্যে হতে হবে।',
        'file' => ':attribute অবশ্যই :min থেকে :max কিলোবাইটের মধ্যে হতে হবে।',
        'numeric' => ':attribute অবশ্যই :min থেকে :max এর মধ্যে হতে হবে।',
        'string' => ':attribute অবশ্যই :min থেকে :max অক্ষরের মধ্যে হতে হবে।',
    ],
    'boolean' => ':attribute অবশ্যই true অথবা false হতে হবে।',
    'can' => ':attribute ফিল্ডে একটি অননুমোদিত মান রয়েছে।',
    'confirmed' => ':attribute নিশ্চিতকরণ মিলেনি।',
    'contains' => ':attribute ফিল্ডে একটি প্রয়োজনীয় মান অনুপস্থিত।',
    'current_password' => 'পাসওয়ার্ডটি সঠিক নয়।',
    'date' => ':attribute অবশ্যই একটি বৈধ তারিখ হতে হবে।',
    'date_equals' => ':attribute অবশ্যই :date তারিখের সমান হতে হবে।',
    'date_format' => ':attribute অবশ্যই :format ফরম্যাটের সাথে মিলতে হবে।',
    'decimal' => ':attribute অবশ্যই :decimal দশমিক স্থান থাকতে হবে।',
    'declined' => ':attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'declined_if' => ':other :value হলে :attribute অবশ্যই প্রত্যাখ্যান করতে হবে।',
    'different' => ':attribute এবং :other অবশ্যই ভিন্ন হতে হবে।',
    'digits' => ':attribute অবশ্যই :digits সংখ্যার হতে হবে।',
    'digits_between' => ':attribute অবশ্যই :min থেকে :max সংখ্যার মধ্যে হতে হবে।',
    'dimensions' => ':attribute এর ছবির মাপ সঠিক নয়।',
    'distinct' => ':attribute ফিল্ডে একটি ডুপ্লিকেট মান রয়েছে।',
    'doesnt_end_with' => ':attribute নিম্নলিখিতগুলোর কোনো একটি দিয়ে শেষ হতে পারবে না: :values।',
    'doesnt_start_with' => ':attribute নিম্নলিখিতগুলোর কোনো একটি দিয়ে শুরু হতে পারবে না: :values।',
    'email' => ':attribute অবশ্যই একটি বৈধ ইমেইল ঠিকানা হতে হবে।',
    'ends_with' => ':attribute নিম্নলিখিতগুলোর একটি দিয়ে শেষ হতে হবে: :values।',
    'enum' => 'নির্বাচিত :attribute সঠিক নয়।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',
    'extensions' => ':attribute অবশ্যই নিম্নলিখিত এক্সটেনশনের একটি ফাইল হতে হবে: :values।',
    'file' => ':attribute অবশ্যই একটি ফাইল হতে হবে।',
    'filled' => ':attribute ফিল্ডে একটি মান থাকতে হবে।',
    'gt' => [
        'array' => ':attribute অবশ্যই :value টির বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের বেশি হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর বেশি হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের বেশি হতে হবে।',
    ],
    'gte' => [
        'array' => ':attribute অবশ্যই :value টি বা তার বেশি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইট বা তার বেশি হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর সমান বা তার বেশি হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের সমান বা তার বেশি হতে হবে।',
    ],
    'hex_color' => ':attribute অবশ্যই একটি বৈধ হেক্সাডেসিমাল রং হতে হবে।',
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'in_array' => ':attribute অবশ্যই :other তে বিদ্যমান থাকতে হবে।',
    'integer' => ':attribute অবশ্যই একটি পূর্ণসংখ্যা হতে হবে।',
    'ip' => ':attribute অবশ্যই একটি বৈধ IP ঠিকানা হতে হবে।',
    'ipv4' => ':attribute অবশ্যই একটি বৈধ IPv4 ঠিকানা হতে হবে।',
    'ipv6' => ':attribute অবশ্যই একটি বৈধ IPv6 ঠিকানা হতে হবে।',
    'json' => ':attribute অবশ্যই একটি বৈধ JSON স্ট্রিং হতে হবে।',
    'list' => ':attribute অবশ্যই একটি তালিকা হতে হবে।',
    'lowercase' => ':attribute অবশ্যই ছোট হাতের অক্ষরে হতে হবে।',
    'lt' => [
        'array' => ':attribute অবশ্যই :value টির কম আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের কম হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর কম হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের কম হতে হবে।',
    ],
    'lte' => [
        'array' => ':attribute অবশ্যই :value টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute অবশ্যই :value কিলোবাইটের সমান বা কম হতে হবে।',
        'numeric' => ':attribute অবশ্যই :value এর সমান বা কম হতে হবে।',
        'string' => ':attribute অবশ্যই :value অক্ষরের সমান বা কম হতে হবে।',
    ],
    'mac_address' => ':attribute অবশ্যই একটি বৈধ MAC ঠিকানা হতে হবে।',
    'max' => [
        'array' => ':attribute এ :max টির বেশি আইটেম থাকতে পারবে না।',
        'file' => ':attribute অবশ্যই :max কিলোবাইটের বেশি হতে পারবে না।',
        'numeric' => ':attribute অবশ্যই :max এর বেশি হতে পারবে না।',
        'string' => ':attribute অবশ্যই :max অক্ষরের বেশি হতে পারবে না।',
    ],
    'max_digits' => ':attribute এ :max টির বেশি সংখ্যা থাকতে পারবে না।',
    'mimes' => ':attribute অবশ্যই এই ধরনের ফাইল হতে হবে: :values।',
    'mimetypes' => ':attribute অবশ্যই এই ধরনের ফাইল হতে হবে: :values।',
    'min' => [
        'array' => ':attribute তে অন্তত :min টি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই অন্তত :min কিলোবাইট হতে হবে।',
        'numeric' => ':attribute অবশ্যই অন্তত :min হতে হবে।',
        'string' => ':attribute অবশ্যই অন্তত :min অক্ষরের হতে হবে।',
    ],
    'min_digits' => ':attribute তে অন্তত :min টি সংখ্যা থাকতে হবে।',
    'missing' => ':attribute ফিল্ড অনুপস্থিত থাকতে হবে।',
    'missing_if' => ':other :value হলে :attribute ফিল্ড অনুপস্থিত থাকতে হবে।',
    'missing_unless' => ':other :value না হলে :attribute ফিল্ড অনুপস্থিত থাকতে হবে।',
    'missing_with' => ':values উপস্থিত থাকলে :attribute ফিল্ড অনুপস্থিত থাকতে হবে।',
    'missing_with_all' => ':values উপস্থিত থাকলে :attribute ফিল্ড অনুপস্থিত থাকতে হবে।',
    'multiple_of' => ':attribute অবশ্যই :value এর গুণিতক হতে হবে।',
    'not_in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'not_regex' => ':attribute এর ফরম্যাট সঠিক নয়।',
    'numeric' => ':attribute অবশ্যই একটি সংখ্যা হতে হবে।',
    'password' => [
        'letters' => ':attribute তে অন্তত একটি অক্ষর থাকতে হবে।',
        'mixed' => ':attribute তে অন্তত একটি বড় হাতের এবং একটি ছোট হাতের অক্ষর থাকতে হবে।',
        'numbers' => ':attribute তে অন্তত একটি সংখ্যা থাকতে হবে।',
        'symbols' => ':attribute তে অন্তত একটি চিহ্ন থাকতে হবে।',
        'uncompromised' => 'প্রদত্ত :attribute একটি ডেটা লিকে পাওয়া গেছে। অনুগ্রহ করে একটি ভিন্ন :attribute বেছে নিন।',
    ],
    'present' => ':attribute ফিল্ড অবশ্যই উপস্থিত থাকতে হবে।',
    'present_if' => ':other :value হলে :attribute ফিল্ড অবশ্যই উপস্থিত থাকতে হবে।',
    'present_unless' => ':other :value না হলে :attribute ফিল্ড অবশ্যই উপস্থিত থাকতে হবে।',
    'present_with' => ':values উপস্থিত থাকলে :attribute ফিল্ড অবশ্যই উপস্থিত থাকতে হবে।',
    'present_with_all' => ':values উপস্থিত থাকলে :attribute ফিল্ড অবশ্যই উপস্থিত থাকতে হবে।',
    'prohibited' => ':attribute ফিল্ড নিষিদ্ধ।',
    'prohibited_if' => ':other :value হলে :attribute ফিল্ড নিষিদ্ধ।',
    'prohibited_if_accepted' => ':other গৃহীত হলে :attribute ফিল্ড নিষিদ্ধ।',
    'prohibited_if_declined' => ':other প্রত্যাখ্যাত হলে :attribute ফিল্ড নিষিদ্ধ।',
    'prohibited_unless' => ':values এর মধ্যে না থাকলে :attribute ফিল্ড নিষিদ্ধ।',
    'prohibits' => ':attribute ফিল্ড :other কে উপস্থিত থাকতে নিষেধ করে।',
    'regex' => ':attribute এর ফরম্যাট সঠিক নয়।',
    'required' => ':attribute ফিল্ডটি আবশ্যক।',
    'required_array_keys' => ':attribute ফিল্ডে এই এন্ট্রিগুলো থাকতে হবে: :values।',
    'required_if' => ':other :value হলে :attribute ফিল্ডটি আবশ্যক।',
    'required_if_accepted' => ':other গৃহীত হলে :attribute ফিল্ডটি আবশ্যক।',
    'required_if_declined' => ':other প্রত্যাখ্যাত হলে :attribute ফিল্ডটি আবশ্যক।',
    'required_unless' => ':values এর মধ্যে না থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_with' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_with_all' => ':values উপস্থিত থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_without' => ':values উপস্থিত না থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'required_without_all' => ':values এর কোনোটি উপস্থিত না থাকলে :attribute ফিল্ডটি আবশ্যক।',
    'same' => ':attribute অবশ্যই :other এর সাথে মিলতে হবে।',
    'size' => [
        'array' => ':attribute এ :size টি আইটেম থাকতে হবে।',
        'file' => ':attribute অবশ্যই :size কিলোবাইট হতে হবে।',
        'numeric' => ':attribute অবশ্যই :size হতে হবে।',
        'string' => ':attribute অবশ্যই :size অক্ষরের হতে হবে।',
    ],
    'starts_with' => ':attribute অবশ্যই নিম্নলিখিত একটি দিয়ে শুরু হতে হবে: :values।',
    'string' => ':attribute অবশ্যই একটি স্ট্রিং হতে হবে।',
    'timezone' => ':attribute অবশ্যই একটি বৈধ টাইমজোন হতে হবে।',
    'unique' => ':attribute ইতিমধ্যে ব্যবহৃত হয়েছে।',
    'uploaded' => ':attribute আপলোড করতে ব্যর্থ হয়েছে।',
    'uppercase' => ':attribute অবশ্যই বড় হাতের অক্ষরে হতে হবে।',
    'url' => ':attribute অবশ্যই একটি বৈধ URL হতে হবে।',
    'ulid' => ':attribute অবশ্যই একটি বৈধ ULID হতে হবে।',
    'uuid' => ':attribute অবশ্যই একটি বৈধ UUID হতে হবে।',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes (Bangla field names)
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'title' => 'শিরোনাম',
        'body' => 'পোস্টের বিবরণ',
        'image' => 'ছবি',
        'amount' => 'পরিমাণ',
        'note' => 'নোট',
        'expense_category_id' => 'ক্যাটাগরি',
        'created_at' => 'তারিখ',
        'status' => 'অবস্থা',
    ],

];
