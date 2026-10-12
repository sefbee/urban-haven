<?php
$content = file_get_contents('routes/web.php');

$content = str_replace("use App\Http\Controllers\Admin\HomeSectionController;", "use App\Http\Controllers\Admin\PageSectionController;", $content);

$oldRoutes = <<<'EOD'
        Route::get('home-sections', [HomeSectionController::class, 'index'])->name('home-sections.index');
        Route::prefix('home-sections/{section}')->whereIn('section', HomeSections::keys())->name('home-sections.')->group(function () {
            Route::get('/', [HomeSectionController::class, 'edit'])->name('edit');
            Route::put('/', [HomeSectionController::class, 'update'])->name('update');
            Route::post('visibility', [HomeSectionController::class, 'visibility'])->name('visibility');
            Route::post('move', [HomeSectionController::class, 'move'])->name('move');
            Route::post('publish', [HomeSectionController::class, 'publish'])->name('publish');
            Route::post('discard', [HomeSectionController::class, 'discard'])->name('discard');
            Route::post('reset', [HomeSectionController::class, 'reset'])->name('reset');
        });
EOD;

$newRoutes = <<<'EOD'
        Route::get('page-sections/{page}', [PageSectionController::class, 'index'])->whereIn('page', ['home', 'about', 'contact', 'articles'])->name('page-sections.index');
        Route::prefix('page-sections/{page}/{section}')->whereIn('page', ['home', 'about', 'contact', 'articles'])->name('page-sections.')->group(function () {
            Route::get('/', [PageSectionController::class, 'edit'])->name('edit');
            Route::put('/', [PageSectionController::class, 'update'])->name('update');
            Route::post('visibility', [PageSectionController::class, 'visibility'])->name('visibility');
            Route::post('move', [PageSectionController::class, 'move'])->name('move');
            Route::post('publish', [PageSectionController::class, 'publish'])->name('publish');
            Route::post('discard', [PageSectionController::class, 'discard'])->name('discard');
            Route::post('reset', [PageSectionController::class, 'reset'])->name('reset');
        });
EOD;

$content = str_replace($oldRoutes, $newRoutes, $content);

file_put_contents('routes/web.php', $content);
