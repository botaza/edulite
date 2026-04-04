// File 6 of 8: js/admin.js - CLEANED & FIXED
if (typeof Admin === 'undefined') {
const Admin = {
    check() {
        App.fetch('?action=check_session')
            .then(data => {
                if (data.is_admin) {
                    App.showScreen('screen-admin-dash');
                } else {
                    App.showScreen('screen-admin-login');
                }
            })
            .catch(err => console.error(err));
    },

    login() {
        const pass = document.getElementById('admin-pass');
        if (!pass) return;
        
        App.fetch('?action=login', 'POST', 'password=' + encodeURIComponent(pass.value))
            .then(data => {
                if (data.success) {
                    // ✅ REQUEST 1: Upon successful login, redirect to index.php
                    window.location.href = 'index.php';
                } else {
                    alert('❌ Wrong password');
                }
            })
            .catch(err => alert('Error: ' + err));
    },

    reset(type) {
        if (confirm('⚠️ Reset ' + type + '? This cannot be undone!')) {
            App.fetch('?action=reset', 'POST', 'type=' + type)
                .then(data => {
                    if (data.success) {
                        alert('✅ Reset complete');
                    }
                })
                .catch(err => alert('Error: ' + err));
        }
    },

    uploadPdf() {
        const input = document.createElement('input');
        input.type = 'file'; 
        input.accept = '.pdf';
        
        input.onchange = () => {
            const file = input.files[0];
            if (file && file.type === 'application/pdf') {
                const formData = new FormData();
                formData.append('pdf', file);
                
                fetch('api.php?action=upload_pdf', {
                    method: 'POST',
                    body: formData
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ PDF uploaded: ' + data.filename);
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => alert('❌ Upload failed: ' + err));
            } else {
                alert('Please select a valid PDF file');
            }
        };
        
        input.click();
    },

    deletePdf() {
        if (confirm('🗑️ Delete lesson PDF? Students won\'t be able to view it.')) {
            fetch('api.php?action=delete_pdf', { method: 'POST' })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ PDF deleted');
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => alert('❌ Delete failed: ' + err));
        }
    },

    toggleModules() {
        fetch('api.php?action=get_modules_config')
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    alert('Error loading config');
                    return;
                }
                
                const config = data.config;
                
                const wordcloud = confirm(
                    'Word Cloud Module\n\n' +
                    'Current: ' + (config.wordcloud ? '✅ ON' : '❌ OFF') + '\n\n' +
                    'Click OK to enable, Cancel to disable'
                );
                 
                const pdf = confirm(
                    'PDF Viewer Module\n\n' +
                    'Current: ' + (config.pdf_viewer ? '✅ ON' : '❌ OFF') + '\n\n' +
                    'Click OK to enable, Cancel to disable'
                );
                
                const emoji = confirm(
                    'Emoji Meter Module\n\n' +
                    'Current: ' + (config.emoji_meter ? '✅ ON' : '❌ OFF') + '\n\n' +
                    'Click OK to enable, Cancel to disable'
                );
                
                fetch('api.php?action=update_modules_config', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'wordcloud=' + wordcloud + '&pdf_viewer=' + pdf + '&emoji_meter=' + emoji
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Modules updated!\n\nWord Cloud: ' + (wordcloud ? 'ON' : 'OFF') + 
                              '\nPDF Viewer: ' + (pdf ? 'ON' : 'OFF') + 
                              '\nEmoji Meter: ' + (emoji ? 'ON' : 'OFF'));
                    } else {
                        alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => alert('❌ Error: ' + err));
            })
            .catch(err => alert('❌ Error loading config: ' + err));
    }
};

// Expose Admin to global scope
window.Admin = Admin;
}