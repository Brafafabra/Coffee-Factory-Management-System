document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirm before deleting
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this record?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-calculate payment amount in payments.php
    if (document.getElementById('delivery_id')) {
        document.getElementById('delivery_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                const weight = selectedOption.getAttribute('data-weight');
                const grade = selectedOption.getAttribute('data-grade');
                const rate = {
                    'AA': 350,
                    'AB': 320,
                    'C': 300,
                    'PB': 310,
                    'E': 340,
                    'TT': 290
                }[grade];
                
                document.getElementById('weight').value = weight;
                document.getElementById('grade').value = grade;
                document.getElementById('rate').value = rate.toLocaleString();
                document.getElementById('amount').value = (weight * rate).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            } else {
                document.getElementById('weight').value = '';
                document.getElementById('grade').value = '';
                document.getElementById('rate').value = '';
                document.getElementById('amount').value = '';
            }
        });
    }

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirm before destructive actions
    document.querySelectorAll('.confirm-action').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirmMessage || 'Are you sure you want to perform this action?')) {
                e.preventDefault();
            }
        });
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-calculate fields
    document.querySelectorAll('[data-calculate]').forEach(field => {
        field.addEventListener('change', function() {
            const target = document.querySelector(this.dataset.calculateTarget);
            if (target) {
                // Implement calculation logic based on data-calculate-type
                // Example: data-calculate="weight" data-calculate-target="#amount"
                // Would calculate amount based on weight and grade
            }
        });
    });

    // Responsive table adjustments
    function handleResponsiveTables() {
        document.querySelectorAll('.table-responsive').forEach(table => {
            if (table.offsetWidth < table.scrollWidth) {
                table.classList.add('table-scrollable');
            } else {
                table.classList.remove('table-scrollable');
            }
        });
    }

    window.addEventListener('resize', handleResponsiveTables);
    handleResponsiveTables();
});



document.addEventListener('DOMContentLoaded', function() {
    // Delete farmer with AJAX
    document.querySelectorAll('.delete-farmer').forEach(function(button) {
        button.addEventListener('click', function() {
            const farmerId = this.getAttribute('data-id');
            const farmerRow = this.closest('tr');
            const farmerName = farmerRow.querySelector('td:nth-child(2)').textContent;
            
            // Show confirmation dialog with farmer name
            if (confirm(`Are you sure you want to delete ${farmerName}? This action cannot be undone.`)) {
                // Disable button and show spinner
                button.disabled = true;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';
                
                // AJAX request
                fetch(`delete_farmer.php?id=${farmerId}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-success alert-dismissible fade show';
                        alertDiv.innerHTML = `
                            <strong>Success!</strong> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        
                        // Insert alert at top of table
                        document.querySelector('.card-body').prepend(alertDiv);
                        
                        // Fade out and remove row
                        farmerRow.style.transition = 'opacity 0.5s';
                        farmerRow.style.opacity = '0';
                        setTimeout(() => farmerRow.remove(), 500);
                    } else {
                        throw new Error(data.message || 'Failed to delete farmer');
                    }
                })
                .catch(error => {
                    // Reset button
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-trash-fill me-1"></i> Delete';
                    
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                    alertDiv.innerHTML = `
                        <strong>Error!</strong> ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.querySelector('.card-body').prepend(alertDiv);
                });
            }
        });
    });
});