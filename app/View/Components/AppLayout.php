<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppLayout extends Component
{
    public function __construct(public string $title = 'Sistem Monitoring Pencairan')
    {
    }

    public function render(): View
    {
        return view('layouts.app');
    }
}
