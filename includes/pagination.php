<?php
// includes/pagination.php — Reusable Pagination Component

/**
 * Calculate pagination data
 * @param int $totalItems Total number of items
 * @param int $perPage Items per page
 * @param int $currentPage Current page number
 * @return array ['totalPages', 'offset', 'currentPage']
 */
function getPaginationData($totalItems, $perPage = 9, $currentPage = 1)
{
    $totalPages = max(1, ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'totalPages' => $totalPages,
        'offset' => $offset,
        'currentPage' => $currentPage,
        'perPage' => $perPage,
        'totalItems' => $totalItems
    ];
}

/**
 * Render pagination HTML
 * @param int $currentPage Current page
 * @param int $totalPages Total pages
 * @param string $baseUrl Base URL (without page param)
 */
function renderPagination($currentPage, $totalPages, $baseUrl = '')
{
    if ($totalPages <= 1)
        return;

    // Build base URL with existing query params
    $separator = (strpos($baseUrl, '?') !== false) ? '&' : '?';

    echo '<nav class="flex items-center justify-center gap-2 mt-12" aria-label="Pagination">';

    // Previous button
    if ($currentPage > 1) {
        echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage - 1) . '" 
              class="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-xl text-sm font-bold uppercase tracking-widest text-paw-gray hover:bg-paw-accent hover:text-white hover:border-paw-accent transition-all">
              <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
              </a>';
    } else {
        echo '<span class="flex items-center gap-1 px-4 py-2 border border-gray-100 rounded-xl text-sm font-bold uppercase tracking-widest text-gray-300 cursor-not-allowed">
              <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
              </span>';
    }

    // Page numbers
    $range = 2; // Show 2 pages before and after current
    $startPage = max(1, $currentPage - $range);
    $endPage = min($totalPages, $currentPage + $range);

    if ($startPage > 1) {
        echo '<a href="' . $baseUrl . $separator . 'page=1" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold hover:bg-paw-accent hover:text-white transition-all border border-gray-200">1</a>';
        if ($startPage > 2) {
            echo '<span class="w-8 text-center text-gray-400">…</span>';
        }
    }

    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            echo '<span class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold bg-paw-accent text-white shadow-lg shadow-paw-accent/20">' . $i . '</span>';
        } else {
            echo '<a href="' . $baseUrl . $separator . 'page=' . $i . '" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold hover:bg-paw-accent hover:text-white transition-all border border-gray-200">' . $i . '</a>';
        }
    }

    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            echo '<span class="w-8 text-center text-gray-400">…</span>';
        }
        echo '<a href="' . $baseUrl . $separator . 'page=' . $totalPages . '" class="w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold hover:bg-paw-accent hover:text-white transition-all border border-gray-200">' . $totalPages . '</a>';
    }

    // Next button
    if ($currentPage < $totalPages) {
        echo '<a href="' . $baseUrl . $separator . 'page=' . ($currentPage + 1) . '" 
              class="flex items-center gap-1 px-4 py-2 border border-gray-200 rounded-xl text-sm font-bold uppercase tracking-widest text-paw-gray hover:bg-paw-accent hover:text-white hover:border-paw-accent transition-all">
              Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
              </a>';
    } else {
        echo '<span class="flex items-center gap-1 px-4 py-2 border border-gray-100 rounded-xl text-sm font-bold uppercase tracking-widest text-gray-300 cursor-not-allowed">
              Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
              </span>';
    }

    echo '</nav>';

    // Show page info
    echo '<p class="text-center text-xs text-paw-gray mt-3">Page ' . $currentPage . ' of ' . $totalPages . '</p>';
}
?>