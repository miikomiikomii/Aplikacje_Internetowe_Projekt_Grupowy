<?php

class SetupController extends Controller
{

    public function resetAction(): void
    {
        DB::reset();
        $this->redirect(url('titles', 'index'));
    }

}