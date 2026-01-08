<?php

class SetupController extends Controller
{

    public function resetAction(): void
    {
        $dbPath = __DIR__ . '/../../data/plusflix.db';
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        DB::conn();
        $this->redirect(url('titles', 'index'));
    }

}