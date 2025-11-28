// Main JavaScript for Hjelpelærer Søknadssystem

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // File upload handling
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                
                // Find the label or display element
                const label = document.querySelector(`label[for="${input.id}"]`);
                if (label) {
                    label.innerHTML = `<i class="fas fa-file"></i> ${fileName} (${fileSize} MB)`;
                }
            }
        });
    });

    // Search functionality
    const searchInput = document.getElementById('jobSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const jobCards = document.querySelectorAll('.job-card');
            
            jobCards.forEach(function(card) {
                const title = card.querySelector('.card-title').textContent.toLowerCase();
                const description = card.querySelector('.card-text').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            const itemName = button.getAttribute('data-item-name') || 'dette elementet';
            if (!confirm(`Er du sikker på at du vil slette ${itemName}?`)) {
                e.preventDefault();
            }
        });
    });

    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[data-max-length]');
    textareas.forEach(function(textarea) {
        const maxLength = parseInt(textarea.getAttribute('data-max-length'));
        const counter = document.createElement('small');
        counter.className = 'text-muted character-counter';
        counter.textContent = `0/${maxLength} tegn`;
        
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const currentLength = textarea.value.length;
            counter.textContent = `${currentLength}/${maxLength} tegn`;
            
            if (currentLength > maxLength * 0.9) {
                counter.className = 'text-warning character-counter';
            } else {
                counter.className = 'text-muted character-counter';
            }
        });
    });

    // Dynamic form fields
    const addFieldButtons = document.querySelectorAll('.add-field');
    addFieldButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const template = document.querySelector('.field-template');
            if (template) {
                const clone = template.cloneNode(true);
                clone.classList.remove('field-template', 'd-none');
                template.parentNode.insertBefore(clone, template);
            }
        });
    });

    // Remove field functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-field')) {
            e.preventDefault();
            e.target.closest('.field-group').remove();
        }
    });

    // Loading states for forms
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const form = button.closest('form');
            if (form && form.checkValidity()) {
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sender...';
                button.disabled = true;
            }
        });
    });

    // Favorite jobs functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    favoriteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const icon = button.querySelector('i');
            const isFavorited = button.classList.contains('active');
            
            if (isFavorited) {
                // Remove from favorites
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.setAttribute('title', 'Legg til i favoritter');
            } else {
                // Add to favorites
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.setAttribute('title', 'Fjern fra favoritter');
            }
            
            // Here you could add AJAX call to save favorite status
            // saveFavoriteStatus(jobId, !isFavorited);
        });
    });
});

// Utility functions
function showLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = 'block';
    }
}

function hideLoading() {
    const loading = document.getElementById('loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    
    const alertClass = `alert-${type}`;
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.innerHTML = alertHtml;
}

// Export functions for use in other scripts
window.JobSystem = {
    showLoading,
    hideLoading,
    showAlert
};