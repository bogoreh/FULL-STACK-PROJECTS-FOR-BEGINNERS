        </main>
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> Employee Management System. All rights reserved.</p>
            <p class="footer-info">A beginner-friendly PHP project</p>
        </footer>
    </div>
    
    <script>
        // Confirm before deleting an employee
        function confirmDelete(id, name) {
            if (confirm(`Are you sure you want to delete employee: ${name}?`)) {
                window.location.href = `operations/delete_employee.php?id=${id}`;
            }
        }
        
        // Show success/error messages
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.alert');
            messages.forEach(message => {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s';
                    message.style.opacity = '0';
                    setTimeout(() => {
                        if (message.parentNode) {
                            message.parentNode.removeChild(message);
                        }
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>