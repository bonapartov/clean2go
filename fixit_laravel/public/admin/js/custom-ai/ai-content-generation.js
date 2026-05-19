/**
 * Unified AI Content Generation Functions
 * Three main functions: generateTitle, generateDescription, generateContent (HTML-based)
 * All accept dynamic prompts via data attributes
 */

/**
 * Generate Title using AI
 * Usage: <button class="ai-generate-title-btn" data-value="input text" data-prompt="optional prompt" data-content_type="service" data-locale="en" data-length="50">Generate Title</button>
 * 
 * Attributes:
 * - data-length: (optional) Maximum length in characters for the generated title (min: 10, max: 200)
 * - data-prompt: (optional) Custom prompt template
 * - data-content_type: (optional) Content type (service, service_package, product, category, blog, post, page)
 * - data-locale: (optional) Locale for the generated content
 */
$(document).on('click', '.ai-generate-title-btn, .ai-generate-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $wrapper = $btn.closest('.input-copy-box');
    let $input = null;

    // Try to find input/textarea in wrapper or use data-value attribute
    if ($wrapper.length) {
        $input = $wrapper.find('input[type="text"], textarea').first();
    }

    // Get value from input field or data attribute
    let value = $btn.data('value') || ($input ? $input.val().trim() : '');

    // If still no value, try common input fields
    if (!value) {
        value = $('#title, #name, input[name="title"], input[name="name"]').first().val().trim();
    }

    const prompt = $btn.data('prompt') || null;
    const contentType = $btn.data('content_type') || $btn.data('content-type') || 'service';
    const locale = $btn.data('locale') || null;
    const length = $btn.data('length') || null; // Get max length from data-length attribute
    const url = $btn.data('url') || window.generateTitleUrl || '/admin/custom-ai-model/generate-title';

    if (!value) {
        showToast('Please enter a value first.', 'error');
        return;
    }

    const originalText = $btn.text();
    $btn.prop('disabled', true).text('Generating...').addClass('animate');

    $.ajax({
        method: 'POST',
        url: url,
        data: {
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
            value: value,
            prompt: prompt,
            content_type: contentType,
            locale: locale,
            length: length // Send length to backend
        },
        success: function (res) {
            if (res.status === false) {
                showToast(res.message || 'Invalid input.', 'error');
                return;
            }

            if (!res.data) {
                showToast('AI returned empty response.', 'error');
                return;
            }

            // Set value in input field if found
            if ($input && $input.length) {
                $input.val(res.data);
            } else {
                // Try to set in common title/name fields
                $('#title, #name, input[name="title"], input[name="name"]').first().val(res.data);
            }

            showToast('AI title generated successfully.', 'success');
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Failed to generate. Please try again.';
            showToast(message, 'error');
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        },
        complete: function () {
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        }
    });
});

/**
 * Generate Description using AI (plain text)
 * Usage: <button class="ai-generate-description-btn" data-value="input text" data-prompt="optional prompt" data-content_type="service" data-locale="en">Generate Description</button>
 */
$(document).on('click', '.ai-generate-description-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $wrapper = $btn.closest('.input-copy-box');
    let $textarea = $wrapper.find('textarea').first();

    // Get value from textarea or data attribute
    let value = $btn.data('value') || ($textarea.length ? $textarea.val().trim() : '');

    // Get title for context if available
    const title = $('#title, #name, input[name="title"], input[name="name"]').first().val().trim();

    // If no value, use title as input
    if (!value) {
        value = title || '';
    }

    const prompt = $btn.data('prompt') || null;
    const contentType = $btn.data('content_type') || $btn.data('content-type') || 'service';
    const locale = $btn.data('locale') || null;
    const length = $btn.data('length') || null; // Get max length from data-length attribute
    const url = $btn.data('url') || window.generateDescriptionUrl || '/admin/custom-ai-model/generate-description';

    if (!value) {
        showToast('Please enter a value or title first.', 'error');
        return;
    }

    const originalText = $btn.text();
    $btn.prop('disabled', true).text('Generating...').addClass('animate');

    $.ajax({
        method: 'POST',
        url: url,
        data: {
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
            value: value,
            prompt: prompt,
            content_type: contentType,
            locale: locale,
            title: title,
            length: length // Send length to backend
        },
        success: function (res) {
            if (res.status === false) {
                showToast(res.message || 'Failed to generate description.', 'error');
                return;
            }

            if (!res.data) {
                showToast('AI returned empty response.', 'error');
                return;
            }

            // Set value in textarea if found
            if ($textarea && $textarea.length) {
                $textarea.val(res.data);
            } else {
                // Try to set in common description fields
                $('textarea[name="description"], textarea[name="meta_description"]').first().val(res.data);
            }

            showToast('AI description generated successfully.', 'success');
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Failed to generate description. Please try again.';
            showToast(message, 'error');
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        },
        complete: function () {
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        }
    });
});

/**
 * Generate Content (HTML-based) using AI
 * Usage: <button class="ai-generate-content-btn" data-value="input text" data-prompt="optional prompt" data-content_type="service" data-locale="en">Generate Content</button>
 */
$(document).on('click', '.ai-generate-content-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const $wrapper = $btn.closest('.input-copy-box');
    let $textarea = $wrapper.find('textarea').first();

    // Get value from textarea, TinyMCE editor, or data attribute
    let value = $btn.data('value') || '';

    // Try to get from TinyMCE editor first
    const contentId = $textarea.attr('id') || 'content';
    if (typeof tinymce !== 'undefined' && tinymce.get(contentId)) {
        value = tinymce.get(contentId).getContent().trim();
    } else if ($textarea.length) {
        value = $textarea.val().trim();
    }

    // Get title for context if available
    const title = $('#title, #name, input[name="title"], input[name="name"]').first().val().trim();

    // If no value, use title as input
    if (!value) {
        value = title || '';
    }

    const prompt = $btn.data('prompt') || null;
    const contentType = $btn.data('content_type') || $btn.data('content-type') || 'service';
    const locale = $btn.data('locale') || null;
    const url = $btn.data('url') || window.generateContentUrl || '/admin/custom-ai-model/generate-content';

    if (!value) {
        showToast('Please enter a value or title first.', 'error');
        return;
    }

    const originalText = $btn.text();
    $btn.prop('disabled', true).text('Generating...').addClass('animate');

    $.ajax({
        method: 'POST',
        url: url,
        data: {
            _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
            value: value,
            prompt: prompt,
            content_type: contentType,
            locale: locale,
            title: title
        },
        success: function (res) {
            if (res.status === false) {
                showToast(res.message || 'Failed to generate content.', 'error');
                return;
            }

            if (!res.data) {
                showToast('AI returned empty response.', 'error');
                return;
            }

            // Set value in TinyMCE editor if available, otherwise in textarea
            const editorId = contentId || 'content';
            let contentSet = false;

            // Try TinyMCE first
            if (typeof tinymce !== 'undefined') {
                // Try to get editor by ID
                let editor = tinymce.get(editorId);

                // If not found, try 'content' directly
                if (!editor && editorId !== 'content') {
                    editor = tinymce.get('content');
                }

                // If still not found, try to find by class selector
                if (!editor) {
                    const editors = tinymce.editors;
                    for (let i = 0; i < editors.length; i++) {
                        if (editors[i].id === editorId || editors[i].id === 'content') {
                            editor = editors[i];
                            break;
                        }
                    }
                }

                if (editor) {
                    editor.setContent(res.data);
                    editor.save(); // Save to textarea
                    contentSet = true;
                }
            }

            // Fallback to textarea if TinyMCE didn't work
            if (!contentSet) {
                if ($textarea && $textarea.length) {
                    $textarea.val(res.data);
                    // Trigger change event for validation
                    $textarea.trigger('change');
                    contentSet = true;
                } else {
                    // Try to set in common content fields
                    const $contentField = $('textarea#content, textarea[name="content"]').first();
                    if ($contentField.length) {
                        $contentField.val(res.data);
                        $contentField.trigger('change');
                        contentSet = true;
                    }
                }
            }

            // If still not set, try one more time with direct ID selector
            if (!contentSet && typeof tinymce !== 'undefined') {
                setTimeout(function () {
                    const editor = tinymce.get('content');
                    if (editor) {
                        editor.setContent(res.data);
                        editor.save();
                    } else {
                        $('#content').val(res.data).trigger('change');
                    }
                }, 100);
            }

            showToast('AI content generated successfully.', 'success');
        },
        error: function (xhr) {
            const message = xhr.responseJSON?.message || 'Failed to generate content. Please try again.';
            showToast(message, 'error');
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        },
        complete: function () {
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        }
    });
});

// Handler for FAQ generation
$(document).on('click', '.ai-generate-faq-btn', function (e) {
    e.preventDefault();

    const $btn = $(this);
    const url = $btn.data('url');
    const locale = $btn.data('locale') || null;

    const title = $('#title').val().trim();

    if (!title) {
        showToast('Please enter a service title first.', 'error');
        return;
    }

    if (!url) {
        console.error('AI Generate FAQ: data-url missing.');
        return;
    }

    const originalText = $btn.text();
    $btn.prop('disabled', true).text('Generating...').addClass('animate');

    $.ajax({
        method: 'POST',
        url: url,
        data: {
            _token: "{{ csrf_token() }}",
            title: title,
            locale: locale,
            content_type: 'service',
            count: 5
        },
        success: function (res) {
            if (res.status === false) {
                showToast(res.message || 'Failed to generate FAQs.', 'error');
                return;
            }

            if (!res.data) {
                showToast('AI returned empty FAQ response.', 'error');
                return;
            }

            let faqs = res.data;

            // If backend returned string, try to parse JSON
            if (typeof faqs === 'string') {
                try {
                    faqs = JSON.parse(faqs);
                } catch (e) {
                    showToast('Could not parse FAQ response.', 'error');
                    return;
                }
            }

            if (!Array.isArray(faqs) || faqs.length === 0) {
                showToast('No FAQs generated.', 'error');
                return;
            }

            const $container = $('.faq-container');
            const $template = $container.find('.faqs-structure').first().clone();

            // Clear existing FAQs
            $container.empty();

            faqs.forEach(function (item, index) {
                const $item = $template.clone();

                // Update names/indexes
                $item.find('input[name^="faqs"]').each(function () {
                    const $input = $(this);
                    let name = $input.attr('name') || '';
                    name = name.replace(/\[\d+\]/, '[' + index + ']');
                    $input.attr('name', name);

                    if (/\[question\]$/.test(name)) {
                        $input.val(item.question || '');
                    }

                    if (/\[id\]$/.test(name)) {
                        $input.val(''); // reset ID for new FAQs
                    }
                });

                $item.find('textarea[name^="faqs"]').each(function () {
                    const $textarea = $(this);
                    let name = $textarea.attr('name') || '';
                    name = name.replace(/\[\d+\]/, '[' + index + ']');
                    $textarea.attr('name', name);

                    if (/\[answer\]$/.test(name)) {
                        $textarea.val(item.answer || '');
                    }
                });

                $container.append($item);
            });

            showToast('FAQs generated successfully.', 'success');
        },
        error: function () {
            showToast('Failed to generate FAQs. Please try again.', 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).text(originalText).removeClass('animate');
        }
    });
});

/**
 * Simple toast helper for admin panel
 * type: 'success' | 'error' | 'info' | 'warning'
 */
function showToast(message, type) {
    type = type || 'info';

    // If global toastr is available, use it for consistency
    if (typeof window.toastr !== 'undefined') {
        if (type === 'success' && typeof toastr.success === 'function') {
            toastr.success(message);
            return;
        }
        if (type === 'error' && typeof toastr.error === 'function') {
            toastr.error(message);
            return;
        }
        if (type === 'warning' && typeof toastr.warning === 'function') {
            toastr.warning(message);
            return;
        }
        if (typeof toastr.info === 'function') {
            toastr.info(message);
            return;
        }
    }

    // Create wrapper if not exists
    let $wrapper = $('#ai-toast-wrapper');
    if (!$wrapper.length) {
        $wrapper = $('<div id="ai-toast-wrapper"></div>').css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            zIndex: 9999
        });
        $('body').append($wrapper);
    }

    const bgColors = {
        success: '#198754',
        error: '#dc3545',
        info: '#0d6efd',
        warning: '#ffc107'
    };

    const $toast = $('<div class="ai-toast"></div>').text(message).css({
        backgroundColor: bgColors[type] || bgColors.info,
        color: '#fff',
        padding: '10px 16px',
        marginTop: '8px',
        borderRadius: '4px',
        boxShadow: '0 2px 6px rgba(0,0,0,0.2)',
        fontSize: '14px',
        maxWidth: '260px',
        wordBreak: 'break-word',
        opacity: 0,
        transition: 'opacity 0.2s ease-in-out'
    });

    $wrapper.append($toast);

    // Fade in
    requestAnimationFrame(function () {
        $toast.css('opacity', 1);
    });

    // Auto-remove after 3 seconds
    setTimeout(function () {
        $toast.css('opacity', 0);
        setTimeout(function () {
            $toast.remove();
        }, 200);
    }, 3000);
}