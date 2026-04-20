<?php
/**
 * AttachmentGallery - Handles attachment file discovery and rendering
 */

namespace Diplodocus;

class AttachmentGallery
{
    private string $basePath;
    private array $allowedTypes = [
        // Images
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico',
        // Videos
        'mp4', 'webm', 'ogg', 'mov',
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
        // Data files
        'csv', 'json', 'xml',
        // Web
        'html', 'htm',
        // Text
        'txt', 'md',
    ];
    
    private array $imageTypes = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'ico'];
    private array $videoTypes = ['mp4', 'webm', 'ogg', 'mov'];
    private array $dataTypes = ['csv', 'json', 'xml'];
    private array $webTypes = ['html', 'htm'];
    private array $docTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
    }
    
    /**
     * Get all attachments for a project
     */
    public function getAttachments(string $project): array
    {
        $attachmentsPath = $this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments';
        
        if (!is_dir($attachmentsPath)) {
            return [];
        }
        
        $attachments = [
            'images' => [],
            'videos' => [],
            'data' => [],
            'documents' => [],
            'web' => [],
            'other' => [],
        ];
        
        $files = $this->scanDirectory($attachmentsPath);
        
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $this->allowedTypes)) {
                continue;
            }
            
            $relativePath = str_replace($attachmentsPath . DIRECTORY_SEPARATOR, '', $file);
            $fileInfo = $this->getFileInfo($file, $relativePath, $project);
            
            if (in_array($ext, $this->imageTypes)) {
                $attachments['images'][] = $fileInfo;
            } elseif (in_array($ext, $this->videoTypes)) {
                $attachments['videos'][] = $fileInfo;
            } elseif (in_array($ext, $this->dataTypes)) {
                $attachments['data'][] = $fileInfo;
            } elseif (in_array($ext, $this->docTypes)) {
                $attachments['documents'][] = $fileInfo;
            } elseif (in_array($ext, $this->webTypes)) {
                $attachments['web'][] = $fileInfo;
            } else {
                $attachments['other'][] = $fileInfo;
            }
        }
        
        return $attachments;
    }
    
    /**
     * Get file info array
     */
    private function getFileInfo(string $fullPath, string $relativePath, string $project): array
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        
        return [
            'name' => basename($fullPath),
            'path' => $relativePath,
            'fullPath' => $fullPath,
            'url' => "?project={$project}&file=attachments/" . urlencode($relativePath),
            'extension' => $ext,
            'size' => filesize($fullPath),
            'sizeFormatted' => $this->formatFileSize(filesize($fullPath)),
            'modified' => filemtime($fullPath),
            'type' => $this->getFileType($ext),
            'icon' => $this->getFileIcon($ext),
        ];
    }
    
    /**
     * Scan directory recursively
     */
    private function scanDirectory(string $dir): array
    {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }
        
        $items = scandir($dir);
        
        foreach ($items as $item) {
            if ($item[0] === '.') continue;
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($path)) {
                $files = array_merge($files, $this->scanDirectory($path));
            } else {
                $files[] = $path;
            }
        }
        
        return $files;
    }
    
    /**
     * Get file type category
     */
    private function getFileType(string $ext): string
    {
        if (in_array($ext, $this->imageTypes)) return 'image';
        if (in_array($ext, $this->videoTypes)) return 'video';
        if (in_array($ext, $this->dataTypes)) return 'data';
        if (in_array($ext, $this->docTypes)) return 'document';
        if (in_array($ext, $this->webTypes)) return 'web';
        return 'other';
    }
    
    /**
     * Get icon for file type
     */
    private function getFileIcon(string $ext): string
    {
        $icons = [
            // Images
            'png' => '🖼️', 'jpg' => '🖼️', 'jpeg' => '🖼️', 'gif' => '🖼️', 
            'svg' => '🖼️', 'webp' => '🖼️', 'ico' => '🖼️',
            // Videos
            'mp4' => '🎬', 'webm' => '🎬', 'ogg' => '🎬', 'mov' => '🎬',
            // Data
            'csv' => '📊', 'json' => '📋', 'xml' => '📄',
            // Documents
            'pdf' => '📕', 'doc' => '📘', 'docx' => '📘',
            'xls' => '📗', 'xlsx' => '📗', 'ppt' => '📙', 'pptx' => '📙',
            // Web
            'html' => '🌐', 'htm' => '🌐',
            // Text
            'txt' => '📝', 'md' => '📝',
        ];
        
        return $icons[$ext] ?? '📎';
    }
    
    /**
     * Format file size
     */
    private function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Read CSV file and return as array
     */
    public function readCsv(string $project, string $file): array
    {
        // Handle path - file might already include 'attachments/' prefix or not
        $file = ltrim($file, '/\\');
        if (strpos($file, 'attachments/') === 0 || strpos($file, 'attachments\\') === 0) {
            $file = substr($file, 12); // Remove 'attachments/' prefix
        }
        
        $path = $this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $file;
        
        // Security check
        $realPath = realpath($path);
        $attachmentsDir = realpath($this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments');
        
        if (!$realPath || !$attachmentsDir || strpos($realPath, $attachmentsDir) !== 0) {
            return ['error' => 'File not found or access denied: ' . $file];
        }
        
        if (!file_exists($realPath) || strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'csv') {
            return ['error' => 'Invalid CSV file'];
        }
        
        $data = [];
        $headers = [];
        
        if (($handle = fopen($realPath, 'r')) !== false) {
            $row = 0;
            // Provide all parameters to avoid deprecation warning in PHP 8.4+
            while (($line = fgetcsv($handle, 0, ',', '"', '\\')) !== false) {
                if ($row === 0) {
                    // Strip BOM from first header if present
                    if (!empty($line[0])) {
                        $line[0] = preg_replace('/^\x{FEFF}/u', '', $line[0]);
                    }
                    $headers = $line;
                } else {
                    // Pad or trim line to match headers count
                    $lineCount = count($line);
                    $headerCount = count($headers);
                    
                    if ($lineCount < $headerCount) {
                        $line = array_pad($line, $headerCount, '');
                    } elseif ($lineCount > $headerCount) {
                        $line = array_slice($line, 0, $headerCount);
                    }
                    
                    $data[] = array_combine($headers, $line);
                }
                $row++;
            }
            fclose($handle);
        }
        
        return [
            'headers' => $headers,
            'data' => $data,
            'rowCount' => count($data),
        ];
    }
    
    /**
     * Read JSON file
     */
    public function readJson(string $project, string $file): array
    {
        // Handle path - file might already include 'attachments/' prefix or not
        $file = ltrim($file, '/\\');
        if (strpos($file, 'attachments/') === 0 || strpos($file, 'attachments\\') === 0) {
            $file = substr($file, 12); // Remove 'attachments/' prefix
        }
        
        $path = $this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $file;
        
        // Security check
        $realPath = realpath($path);
        $attachmentsDir = realpath($this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments');
        
        if (!$realPath || !$attachmentsDir || strpos($realPath, $attachmentsDir) !== 0) {
            return ['error' => 'File not found or access denied'];
        }
        
        if (!file_exists($realPath) || strtolower(pathinfo($realPath, PATHINFO_EXTENSION)) !== 'json') {
            return ['error' => 'Invalid JSON file'];
        }
        
        $content = file_get_contents($realPath);
        $decoded = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON: ' . json_last_error_msg(), 'raw' => $content];
        }
        
        return [
            'data' => $decoded,
            'raw' => $content,
            'formatted' => json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        ];
    }
    
    /**
     * Get HTML file content for iframe
     */
    public function getHtmlContent(string $project, string $file): ?string
    {
        $path = $this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments' . DIRECTORY_SEPARATOR . $file;
        
        // Security check
        $realPath = realpath($path);
        $attachmentsDir = realpath($this->basePath . DIRECTORY_SEPARATOR . $project . DIRECTORY_SEPARATOR . 'attachments');
        
        if (!$realPath || !$attachmentsDir || strpos($realPath, $attachmentsDir) !== 0) {
            return null;
        }
        
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
        if (!in_array($ext, ['html', 'htm'])) {
            return null;
        }
        
        return file_get_contents($realPath);
    }
    
    /**
     * Render attachment gallery HTML
     */
    public function renderGallery(string $project): string
    {
        $attachments = $this->getAttachments($project);
        $totalCount = array_sum(array_map('count', $attachments));
        
        if ($totalCount === 0) {
            return '<div class="text-gray-500 dark:text-gray-400 text-center py-8">No attachments found.</div>';
        }
        
        $html = '<div data-attachment-gallery class="attachment-gallery" data-project="' . htmlspecialchars($project) . '">';
        
        // Tabs
        $html .= '<div class="attachment-tabs flex border-b border-gray-200 dark:border-gray-700 mb-4">';
        $tabs = [
            'all' => ['label' => 'All', 'count' => $totalCount],
            'images' => ['label' => 'Images', 'count' => count($attachments['images'])],
            'videos' => ['label' => 'Videos', 'count' => count($attachments['videos'])],
            'data' => ['label' => 'Data', 'count' => count($attachments['data'])],
            'documents' => ['label' => 'Documents', 'count' => count($attachments['documents'])],
            'web' => ['label' => 'Web', 'count' => count($attachments['web'])],
        ];
        
        foreach ($tabs as $key => $tab) {
            if ($tab['count'] === 0 && $key !== 'all') continue;
            $active = $key === 'all' ? 'active border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400';
            $html .= '<button data-attachment-tab class="attachment-tab px-4 py-2 text-sm font-medium border-b-2 ' . $active . ' hover:text-blue-600 dark:hover:text-blue-400 transition-colors" data-tab="' . $key . '">';
            $html .= htmlspecialchars($tab['label']);
            $html .= '<span class="ml-1 px-1.5 py-0.5 text-xs bg-gray-100 dark:bg-gray-700 rounded-full">' . $tab['count'] . '</span>';
            $html .= '</button>';
        }
        $html .= '</div>';
        
        // Content sections
        $html .= '<div class="attachment-content">';
        
        // Images grid
        if (!empty($attachments['images'])) {
            $html .= '<div data-attachment-section class="attachment-section" data-type="images">';
            $html .= '<div class="image-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">';
            foreach ($attachments['images'] as $img) {
                $html .= $this->renderImageCard($img);
            }
            $html .= '</div></div>';
        }
        
        // Videos
        if (!empty($attachments['videos'])) {
            $html .= '<div data-attachment-section class="attachment-section" data-type="videos">';
            $html .= '<div class="space-y-4">';
            foreach ($attachments['videos'] as $video) {
                $html .= $this->renderVideoCard($video);
            }
            $html .= '</div></div>';
        }
        
        // Data files (CSV, JSON)
        if (!empty($attachments['data'])) {
            $html .= '<div data-attachment-section class="attachment-section" data-type="data">';
            foreach ($attachments['data'] as $file) {
                $html .= $this->renderDataCard($file, $project);
            }
            $html .= '</div>';
        }
        
        // Documents
        if (!empty($attachments['documents'])) {
            $html .= '<div data-attachment-section class="attachment-section" data-type="documents">';
            $html .= '<div class="space-y-2">';
            foreach ($attachments['documents'] as $doc) {
                $html .= $this->renderDocumentCard($doc);
            }
            $html .= '</div></div>';
        }
        
        // Web files (HTML)
        if (!empty($attachments['web'])) {
            $html .= '<div data-attachment-section class="attachment-section" data-type="web">';
            foreach ($attachments['web'] as $file) {
                $html .= $this->renderWebCard($file, $project);
            }
            $html .= '</div>';
        }
        
        $html .= '</div>'; // .attachment-content
        $html .= '</div>'; // .attachment-gallery
        
        // Image lightbox modal
        $html .= $this->renderLightbox();
        
        return $html;
    }
    
    /**
     * Render image card
     */
    private function renderImageCard(array $img): string
    {
        return '<div data-image-card class="attachment-card image-card group relative bg-white dark:bg-gray-800 cursor-pointer" data-src="' . htmlspecialchars($img['url']) . '" data-name="' . htmlspecialchars($img['name']) . '">
            <div class="aspect-square relative overflow-hidden rounded-lg">
                <img src="' . htmlspecialchars($img['url']) . '" alt="' . htmlspecialchars($img['name']) . '" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="absolute bottom-0 left-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    <p class="text-white text-sm font-medium truncate drop-shadow-lg">' . htmlspecialchars($img['name']) . '</p>
                    <p class="text-white/80 text-xs">' . htmlspecialchars($img['sizeFormatted']) . '</p>
                </div>
            </div>
            <button data-tag-attachment class="tag-attachment" title="Tag this attachment" data-attachment="' . htmlspecialchars($img['name']) . '">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
            </button>
        </div>';
    }
    
    /**
     * Render video card with HTML5 video player
     */
    private function renderVideoCard(array $video): string
    {
        $mimeTypes = [
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
        ];
        $mimeType = $mimeTypes[$video['extension']] ?? 'video/mp4';
        
        $html = '<div class="attachment-card video-card bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">';
        $html .= '<div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">';
        $html .= '<div class="flex items-center">';
        $html .= '<span class="text-2xl mr-3">' . $video['icon'] . '</span>';
        $html .= '<div>';
        $html .= '<h4 class="font-medium text-gray-900 dark:text-white">' . htmlspecialchars($video['name']) . '</h4>';
        $html .= '<span class="text-sm text-gray-500 dark:text-gray-400">' . strtoupper($video['extension']) . ' • ' . $video['sizeFormatted'] . '</span>';
        $html .= '</div></div>';
        $html .= '<button data-tag-attachment class="tag-attachment bg-gray-100 hover:bg-blue-500 hover:text-white text-blue-700 rounded-full p-1.5 shadow transition-all" title="Tag this attachment" data-attachment="' . htmlspecialchars($video['name']) . '">';
        $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>';
        $html .= '</button>';
        $html .= '</div>';
        $html .= '<div class="p-4">';
        $html .= '<video class="w-full rounded-lg" controls preload="metadata">';
        $html .= '<source src="' . htmlspecialchars($video['url']) . '" type="' . $mimeType . '">';
        $html .= 'Your browser does not support the video tag.';
        $html .= '</video>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render data file card (CSV/JSON)
     */
    private function renderDataCard(array $file, string $project): string
    {
        // Use just the filename, not the full path
        $filename = basename($file['path']);
        
        $html = '<div data-file-card class="attachment-card data-card bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-4">';
        $html .= '<div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">';
        $html .= '<div class="flex items-center">';
        $html .= '<span class="text-2xl mr-3">' . $file['icon'] . '</span>';
        $html .= '<div>';
        $html .= '<h4 class="font-medium text-gray-900 dark:text-white">' . htmlspecialchars($file['name']) . '</h4>';
        $html .= '<span class="text-sm text-gray-500 dark:text-gray-400">' . strtoupper($file['extension']) . ' • ' . $file['sizeFormatted'] . '</span>';
        $html .= '</div></div>';
        $html .= '<div class="flex items-center gap-2">';
        $html .= '<button data-file-toggle class="data-toggle px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" data-file="' . htmlspecialchars($filename) . '" data-type="' . $file['extension'] . '" data-project="' . htmlspecialchars($project) . '">View</button>';
        $html .= '<button data-tag-attachment class="tag-attachment bg-gray-100 hover:bg-blue-500 hover:text-white text-blue-700 rounded-full p-1.5 shadow transition-all" title="Tag this attachment" data-attachment="' . htmlspecialchars($file['name']) . '">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
        </button>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div data-file-preview class="data-preview hidden p-4 max-h-96 overflow-auto"></div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render document card
     */
    private function renderDocumentCard(array $doc): string
    {
        return '<a href="' . htmlspecialchars($doc['url']) . '" target="_blank" class="attachment-card document-card flex items-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 transition-colors">
            <span class="text-2xl mr-3">' . $doc['icon'] . '</span>
            <div class="flex-1">
                <h4 class="font-medium text-gray-900 dark:text-white">' . htmlspecialchars($doc['name']) . '</h4>
                <span class="text-sm text-gray-500 dark:text-gray-400">' . strtoupper($doc['extension']) . ' • ' . $doc['sizeFormatted'] . '</span>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
            </svg>
        </a>';
    }
    
    /**
     * Render web file card (HTML iframe)
     */
    private function renderWebCard(array $file, string $project): string
    {
        $html = '<div data-web-card class="attachment-card web-card bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mb-4">';
        $html .= '<div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">';
        $html .= '<div class="flex items-center">';
        $html .= '<span class="text-2xl mr-3">' . $file['icon'] . '</span>';
        $html .= '<div>';
        $html .= '<h4 class="font-medium text-gray-900 dark:text-white">' . htmlspecialchars($file['name']) . '</h4>';
        $html .= '<span class="text-sm text-gray-500 dark:text-gray-400">HTML Document • ' . $file['sizeFormatted'] . '</span>';
        $html .= '</div></div>';
        $html .= '<div class="flex gap-2">';
        $html .= '<button data-iframe-toggle class="iframe-toggle px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors" data-src="' . htmlspecialchars($file['url']) . '">Preview</button>';
        $html .= '<a href="' . htmlspecialchars($file['url']) . '" target="_blank" class="px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">Open</a>';
        $html .= '</div></div>';
        $html .= '<div data-iframe-preview class="iframe-preview hidden">';
        $html .= '<iframe class="w-full border-0" style="height: 600px;"></iframe>';
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render lightbox modal for images
     */
    private function renderLightbox(): string
    {
        return '<div data-attachment-lightbox class="fixed inset-0 z-50 hidden bg-black/90 flex items-center justify-center">
            <button data-lightbox-close class="lightbox-close absolute top-4 right-4 text-white hover:text-gray-300 p-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
            <button data-lightbox-prev class="lightbox-prev absolute left-4 text-white hover:text-gray-300 p-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
            <button data-lightbox-next class="lightbox-next absolute right-16 text-white hover:text-gray-300 p-2">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </button>
            <div data-lightbox-content class="lightbox-content max-w-6xl max-h-[90vh] overflow-auto">
                <img src="" alt="" class="max-w-full max-h-[85vh] object-contain" />
            </div>
            <div data-lightbox-caption class="lightbox-caption absolute bottom-4 left-0 right-0 text-center text-white text-sm"></div>
        </div>';
    }
}
