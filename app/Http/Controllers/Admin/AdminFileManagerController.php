<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminFileManagerController extends Controller
{

    /**
     * 1. Standalone File Manager (Full Page)
     * URL: /admin/file-manager
     */
    public function index()
    {
        return view('admin.file-manager.index');
    }

    /**
     * 2. CKEditor Integration (Popup)
     * URL: /admin/file-manager/ckeditor
     */
    public function ckeditor()
    {
        return view('admin.file-manager.ckeditor');
    }

    /**
     * 3. Input Button Popup (Select Image)
     * URL: /admin/file-manager/popup
     */
    public function popup()
    {
        return view('admin.file-manager.popup');
    }
}
