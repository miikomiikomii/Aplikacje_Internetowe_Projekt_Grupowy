<?php

class SetupController extends Controller
{

    public function resetAction(): void
    {
        $this->requireAdmin();
        DB::reset();
        $this->redirect(url('admin', 'dashboard'));
    }

}