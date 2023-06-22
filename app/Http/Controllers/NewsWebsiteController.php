<?php

namespace App\Http\Controllers;

use App\Models\NewsWebsite;

class NewsWebsiteController extends Controller
{
    public function getNewsWebsiteFromID($id)
    {
        return NewsWebsite::findOrFail($id);
    }

    public function getNewsWebsiteFromNameOrCreate($name)
    {
        return NewsWebsite::firstOrCreate(['website' => $name]);
    }
}
