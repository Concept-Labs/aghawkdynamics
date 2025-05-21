<?php
namespace App\Controller\Block;

use App\Core\Controller;

class FormsController extends Controller
{
   public function form(): void
   {
       $this->render('block/form/blocktest', [], true);
   }
}
