<?php

return [
    'navigation' => [
        'group' => 'Tartalomkezelés',
        'label' => 'Galériák',
    ],
    'resource' => [
        'title' => 'Cím',
        'singular_label' => 'galéria',
        'heading' => 'Galéria címe',
        'slug' => 'Slug / URL azonosító',
        'description' => 'Leírás',
        'is_active' => 'Aktív',
        'images_count' => 'Képek száma',
        'actions' => [
            'manage_images' => 'Képek',
            'edit' => 'Beállítás',
            'delete' => 'Törlés',
        ],
    ],
    'settings' => [
        'section_title' => 'Képfeldolgozás Beállításai',
        'section_description' => 'Miniatűr generálási és átméretezési szabályok beállítása.',
        'unlock_button' => 'Beállítások feloldása',
        'unlock_modal' => [
            'heading' => 'Feloldja a beállításokat?',
            'description' => 'A beállítások módosítása nem frissíti automatikusan a meglévő képeket, hacsak nem menti és regenerálja azokat.',
            'submit' => 'Igen, feloldom',
        ],
        'thumbnails' => [
            'label' => 'Miniatűrök (Thumbnails) generálása',
            'generate' => 'Miniatűr generálása',
            'width' => 'Bélyegkép szélesség (px)',
            'height' => 'Bélyegkép magasság (px)',
            'crop_type' => 'Méretre vágás módja',
        ],
        'resize_main' => [
            'label' => 'Eredeti kép átméretezése feltöltés után',
            'width' => 'Fő kép szélesség (px)',
            'height' => 'Fő kép magasság (px)',
            'crop_type' => 'Méretre vágás módja',
        ],
        'crop_options' => [
            'aspect_ratio' => 'Méretarány tartása (Fit)',
            'crop_out' => 'Kivágás kitöltéssel (Fill)',
            'crop_in' => 'Befoglaló méretezés (Contain)',
        ],
    ],
    'manager' => [
        'title' => 'Galéria képeinek kezelése',
        'upload_zone' => 'Húzza ide a képeket vagy kattintson a feltöltéshez',
        'upload_manual' => 'kattints a tallózáshoz',
        'no_images' => 'Még nincsenek feltöltött képek ebben a galériában.',
        'image' => [
            'edit' => 'Kép adatainak szerkesztése.',
            'details' => 'Kép adatai.',
            'title' => 'Kép címe',
            'description' => 'Kép leírása',
            'confirm_delete' => 'Biztosan törölni szeretnéd ezt a képet?',
            'data' => [
                "title" => "Egyedi adatok (pl. url, target)",
                "add" => "Új mező",
                "key" => "Kulcs (pl. url)",
                "value" => "Érték (pl. https://...)",
                "empty" => "Nincsenek egyedi tulajdonságok megadva.",
                "remove" => "Eltávolítás"
            ],
            "not_found" => "A kép nem található",
        ],
        'thumbnail' => [
            "name" => "Bélyegkép",
            "edit" => "Bélyegkép szerkesztés",
            "modify" => "Bélyegkép módosítása"
        ],
        'actions' => [
            'gallery_settings' => 'Galéria beállításai',
            'save' => 'Mentés',
            'cancel' => 'Mégse',
            'delete' => 'Eltávolítás',
            'reorder_save' => 'Sorrend mentése',
        ],
        'notifications' => [
            'settings_updated' => 'A galéria beállításai sikeresen frissültek.',
            'image_updated' => 'Kép adatai elmentve.',
            'image_deleted' => 'Kép törölve!',
            'image_reordered' => 'Sorrend frissítve!.',
            'images_uploaded' => 'A képek feltöltése és feldolgozása sikeresen megtörtént.',
            'images_processing' => 'Feltöltés és méretezés folyamatban...',
            'images_processing_wait' => 'A képek feldolgozása eltarthat pár másodpercig',
            'copied' => 'Vágólapra másolva!',
        ],
    ],
    'instructions' => [
        'label' => 'Használati útmutató & Egyedi Sablonok',
        'default_template' => 'Alapértelmezett sablon használata',
        'custom_template' => 'Egyedi nézet / sablon megadása',
        'custom_template_desc' => 'Átadhatsz egyedi Blade sablont második paraméterként',
        'available_variables' => 'Elérhető változók a sablonban',
        'var_gallery' => 'A Gallery modell objektum (cím, beállítások)',
        'var_images' => 'A képek gyűjteménye (rendezve sort_order szerint)',
        'image_properties' => 'A ciklusban elérhető kép tulajdonságok',
    ],
    'misc' => [
        "or" => "vagy"
    ]
];
