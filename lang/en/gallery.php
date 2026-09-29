<?php

return [
    'navigation' => [
        'group' => 'Content Management',
        'label' => 'Galleries',
    ],
    'resource' => [
        'title' => 'Title',
        'singular_label' => 'gallery',
        'heading' => 'Gallery title',
        'slug' => 'Slug',
        'description' => 'Description',
        'is_active' => 'Active',
        'images_count' => 'Images',
        'actions' => [
            'manage_images' => 'Images',
            'edit' => 'Settings',
            'delete' => 'Delete',
        ],
    ],
    'settings' => [
        'section_title' => 'Image Settings',
        'section_description' => 'Configure thumbnail generation and main image resizing rules.',
        'unlock_button' => 'Unlock Settings',
        'unlock_modal' => [
            'heading' => 'Unlock Image Settings?',
            'description' => 'Modifying these settings won\'t automatically update existing images unless you save and regenerate.',
            'submit' => 'Yes, unlock',
        ],
        'thumbnails' => [
            'label' => 'Generate Thumbnails',
            'width' => 'Thumb Width (px)',
            'height' => 'Thumb Height (px)',
            'crop_type' => 'Crop Type',
        ],
        'resize_main' => [
            'label' => 'Resize Image After Upload',
            'width' => 'Main Width (px)',
            'height' => 'Main Height (px)',
            'crop_type' => 'Crop Type',
        ],
        'crop_options' => [
            'aspect_ratio' => 'Aspect Ratio (Fit)',
            'crop_out' => 'Crop Out (Fill)',
            'crop_in' => 'Crop In (Contain)',
        ],
    ],
    'manager' => [
        'title' => 'Manage Gallery Images',
        'upload_zone' => 'Drag and drop images here or click to upload',
        'image' => [
            'edit' => 'Edit image details.',
            'title' => 'Image title',
            'description' => 'Image description',
            'confirm_delete' => 'Are you sure you want to delete this image?',
        ],
        'actions' => [
            'gallery_settings' => 'Gallery Settings',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'reorder_save' => 'Save Order',
        ],
        'notifications' => [
            'settings_updated' => 'Gallery settings updated successfully.',
            'image_updated' => 'Image updated.',
            'image_deleted' => 'Image deleted!',
            'image_reordered' => 'Image order changed!.',
            'images_uploaded' => 'Images uploaded and processed successfully.',
            'images_processing' => 'Uploading and resizing in progress...',
            'images_processing_wait' => 'Processing of the images may take a second...',
            'copied' => 'Copied to clipboard!',
        ],
    ],
    'instructions' => [
        'label' => 'Usage Guide & Custom Templates',
        'default_template' => 'Using the default template',
        'custom_template' => 'Specifying a custom view / template',
        'custom_template_desc' => 'You can pass a custom Blade template as the second parameter',
        'available_variables' => 'Available variables in the template',
        'var_gallery' => 'The Gallery model object (title, settings)',
        'var_images' => 'The collection of images (ordered by sort_order)',
        'image_properties' => 'Image properties available inside the loop',
    ],
    'misc' => [
        "or" => "or"
    ]
];
