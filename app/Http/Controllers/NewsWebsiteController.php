<?php

namespace App\Http\Controllers;

use App\Models\NewsWebsite;
use Illuminate\Http\Request;

class NewsWebsiteController extends Controller
{
    /**
     * Find the article by its id.
     */
    public function getNewsWebsiteFromID($id)
    {
        return NewsWebsite::findOrFail($id);
    }

    public function getNewsWebsiteFromNameOrCreate($name)
    {
        return NewsWebsite::firstOrCreate(['website' => $name]);
    }
}
