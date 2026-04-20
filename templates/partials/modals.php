<?php
/**
 * Modals Partial Template
 * 
 * Variables available:
 * - $validationResults: Validation results array
 * - $hasSecurityIssues: Boolean
 * - $hasLintIssues: Boolean
 */

use Diplodocus\TemplateEngine as T;
?>
<?php if ($validationResults): ?>
<!-- Validation Results Modal -->
<div id="validation-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <?php if ($hasSecurityIssues): ?>
                <svg class="w-6 h-6 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-red-500">Security Issues Found</span>
                <?php elseif ($hasLintIssues): ?>
                <svg class="w-6 h-6 text-yellow-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <span class="text-yellow-500">Lint Warnings</span>
                <?php else: ?>
                <svg class="w-6 h-6 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-green-500">All Checks Passed</span>
                <?php endif; ?>
            </h3>
            <button onclick="closeValidationModal()" class="text-gray-400 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="px-6 py-4 overflow-y-auto max-h-96">
            <!-- Stats -->
            <div class="flex items-center space-x-4 mb-4 text-sm text-gray-500">
                <span><?= $validationResults['stats']['files'] ?? 0 ?> files scanned</span>
                <span>•</span>
                <span><?= $validationResults['stats']['issues'] ?? 0 ?> issues found</span>
            </div>
            
            <?php if ($hasSecurityIssues): ?>
            <!-- Security Issues -->
            <div class="mb-6">
                <h4 class="text-sm font-semibold text-red-600 uppercase tracking-wider mb-2">Security Issues</h4>
                <div class="space-y-2">
                    <?php foreach ($validationResults['security'] as $issue): ?>
                    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-red-700"><?= T::e($issue['message'] ?? $issue) ?></p>
                                <?php if (isset($issue['file'])): ?>
                                <p class="text-xs text-red-500 mt-1"><?= T::e($issue['file']) ?>:<?= $issue['line'] ?? '?' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($hasLintIssues): ?>
            <!-- Lint Issues -->
            <div>
                <h4 class="text-sm font-semibold text-yellow-600 uppercase tracking-wider mb-2">Lint Warnings</h4>
                <div class="space-y-2">
                    <?php foreach ($validationResults['lint'] as $issue): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm text-yellow-700"><?= T::e($issue['message'] ?? $issue) ?></p>
                                <?php if (isset($issue['file'])): ?>
                                <p class="text-xs text-yellow-600 mt-1"><?= T::e($issue['file']) ?>:<?= $issue['line'] ?? '?' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!$hasSecurityIssues && !$hasLintIssues): ?>
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-600">All documentation passes security and lint checks!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <?php if ($hasSecurityIssues): ?>
            <span class="text-sm text-red-600 mr-4">Security issues must be resolved before proceeding.</span>
            <?php endif; ?>
            <button onclick="closeValidationModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function closeValidationModal() {
    document.getElementById('validation-modal').remove();
    // Remove validate param from URL
    const url = new URL(window.location);
    url.searchParams.delete('validate');
    window.history.replaceState({}, '', url);
}
</script>
<?php endif; ?>
