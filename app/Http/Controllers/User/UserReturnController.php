<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\websiteBooks;
use Illuminate\Http\Request;

class UserReturnController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {
        $websiteBookdiscount = websiteBooks::pluck('discount', 'article_id')->toArray();

        $returnInvoices = $user->orderInvoices()
        ->whereHas('returnInvoices')
        ->with([
            'returnInvoices.returnItems'=> function ($query) {
                $query->with(['orderInvoiceItem'=> function ($query) {
                    $query->with(['article'=> function ($query1) {
                        $query1->with('articleStock','articleimage')->select('id', 'designation', 'prixpublic', '_prix_public_ttc', '_code_barre','descriptif', 'b_usr_editeur_id', 'hauteur','largeur','epaisseur','_poids_net','dc_auteur','dc_traducteur','dc_collection','nbpages','dc_parution', 'descriptif_court', 'b_usr_articletheme_id','b_usr_articlefamille_id');
                    },]);
                },]);
            },
            'returnInvoices.returnAttachments','returnInvoices.userPayment','returnInvoices.orderInvoice',
            'returnInvoices.returnResponses' => function ($query) {
                $query->with('returnResponseAttachments');
            },
        ])
        ->get();

        foreach ($returnInvoices as $favorite) {
            if (isset($websiteBookdiscount[$favorite->article_id])) {
                $favorite->discount = $websiteBookdiscount[$favorite->article_id];
            } else {
                $favorite->discount = 0; // Or handle default value if needed
            }
        }

        return $this->showAll($returnInvoices);
    }

}


