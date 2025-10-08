<!-- Modal Upload Foto Esperienza Utente -->
<div id="uploadExperienceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-6 border w-11/12 md:w-2/3 lg:w-1/2 xl:w-2/5 shadow-2xl rounded-lg bg-white">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-xl font-bold text-gray-900">📸 Condividi la tua esperienza</h3>
                <p class="text-sm text-gray-600 mt-1">Carica una foto del tuo viaggio e racconta la tua storia</p>
            </div>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Form -->
        <form id="uploadExperienceForm" enctype="multipart/form-data" class="space-y-6">
            <!-- Hidden fields -->
            <input type="hidden" id="upload_article_id" name="article_id" value="">
            <input type="hidden" id="upload_province_id" name="province_id" value="">
            
            <!-- Photo Upload -->
            <div class="space-y-3">
                <label for="experience_photo" class="block text-sm font-medium text-gray-700">
                    📷 La tua foto *
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                    <div class="space-y-2 text-center">
                        <div id="photo-preview" class="hidden">
                            <img id="preview-image" class="mx-auto h-32 w-auto rounded-lg shadow-md" src="" alt="Anteprima">
                            <button type="button" onclick="removePhoto()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                                Rimuovi foto
                            </button>
                        </div>
                        <div id="photo-upload" class="">
                            <i data-lucide="upload" class="mx-auto h-12 w-12 text-gray-400"></i>
                            <div class="flex text-sm text-gray-600">
                                <label for="experience_photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Carica una foto</span>
                                    <input id="experience_photo" name="photo" type="file" class="sr-only" accept="image/jpeg,image/jpg,image/png,image/webp" required>
                                </label>
                                <p class="pl-1">o trascina qui</p>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, WebP fino a 5MB</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- User Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="user_name" class="block text-sm font-medium text-gray-700">
                        👤 Il tuo nome *
                    </label>
                    <input type="text" id="user_name" name="user_name" required 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900"
                           placeholder="Come ti chiami?">
                </div>
                <div>
                    <label for="user_email" class="block text-sm font-medium text-gray-700">
                        ✉️ La tua email *
                    </label>
                    <input type="email" id="user_email" name="user_email" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900"
                           placeholder="esempio@email.com">
                </div>
            </div>
            
            <!-- Description -->
            <div>
                <label for="experience_description" class="block text-sm font-medium text-gray-700">
                    📝 Racconta la tua esperienza *
                </label>
                <textarea id="experience_description" name="description" rows="4" required
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-gray-900"
                          placeholder="Descrivici cosa hai provato, cosa ti è piaciuto di più, qualche consiglio per altri visitatori..."></textarea>
                <p class="mt-2 text-sm text-gray-500">Minimo 20 caratteri. Condividi dettagli utili per altri viaggiatori!</p>
            </div>
            
            <!-- Privacy Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="info" class="h-5 w-5 text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">Prima di pubblicare</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>La tua foto sarà controllata dal nostro team prima della pubblicazione</li>
                                <li>Pubblicheremo solo foto appropriate e di buona qualità</li>
                                <li>Il tuo nome apparirà accanto alla foto pubblicata</li>
                                <li>La tua email non sarà mai mostrata pubblicamente</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Form Status -->
            <div id="upload-status" class="hidden">
                <div id="upload-success" class="hidden rounded-md bg-green-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Foto caricata con successo!</h3>
                            <p class="mt-2 text-sm text-green-700">Grazie per aver condiviso la tua esperienza. La foto sarà pubblicata dopo la nostra revisione.</p>
                        </div>
                    </div>
                </div>
                
                <div id="upload-error" class="hidden rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errore durante il caricamento</h3>
                            <p id="error-message" class="mt-2 text-sm text-red-700"></p>
                        </div>
                    </div>
                </div>
                
                <div id="upload-loading" class="hidden rounded-md bg-yellow-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-yellow-400"></div>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Caricamento in corso...</h3>
                            <p class="mt-2 text-sm text-yellow-700">Stiamo caricando la tua foto, attendi qualche secondo.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex items-center justify-between pt-4">
                <button type="button" onclick="closeUploadModal()" 
                        class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                    Annulla
                </button>
                <button type="submit" id="submit-btn"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                    📤 Carica la mia foto
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// User Upload Experience Modal JavaScript
window.UserUploadModal = {
    modal: null,
    form: null,
    
    init() {
        this.modal = document.getElementById('uploadExperienceModal');
        this.form = document.getElementById('uploadExperienceForm');
        
        if (this.form) {
            this.bindEvents();
        }
    },
    
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitForm();
        });
        
        // Photo preview
        document.getElementById('experience_photo').addEventListener('change', (e) => {
            this.previewPhoto(e.target);
        });
        
        // Drag and drop
        const dropZone = this.modal.querySelector('.border-dashed');
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-400', 'bg-blue-50');
        });
        
        dropZone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-400', 'bg-blue-50');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-400', 'bg-blue-50');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const photoInput = document.getElementById('experience_photo');
                photoInput.files = files;
                this.previewPhoto(photoInput);
            }
        });
    },
    
    open(articleId = null, provinceId = null) {
        // Set context
        document.getElementById('upload_article_id').value = articleId || '';
        document.getElementById('upload_province_id').value = provinceId || '';
        
        // Reset form
        this.resetForm();
        
        // Show modal
        this.modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Focus first input
        setTimeout(() => {
            document.getElementById('user_name').focus();
        }, 100);
    },
    
    close() {
        this.modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
        this.resetForm();
    },
    
    previewPhoto(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file
            if (!file.type.match('image.*')) {
                this.showError('Per favore seleziona un file immagine valido.');
                input.value = '';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                this.showError('La foto è troppo grande. Massimo 5MB consentiti.');
                input.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('preview-image').src = e.target.result;
                document.getElementById('photo-preview').classList.remove('hidden');
                document.getElementById('photo-upload').classList.add('hidden');
            };
            reader.readAsDataURL(file);
        }
    },
    
    removePhoto() {
        document.getElementById('experience_photo').value = '';
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('photo-upload').classList.remove('hidden');
    },
    
    async submitForm() {
        const formData = new FormData(this.form);
        const submitBtn = document.getElementById('submit-btn');
        
        // Validate
        if (!this.validateForm(formData)) {
            return;
        }
        
        // Show loading
        this.showStatus('loading');
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('api/upload-user-photo.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showStatus('success');
                setTimeout(() => {
                    this.close();
                }, 3000);
            } else {
                this.showError(result.error || 'Errore durante il caricamento');
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error('Upload error:', error);
            this.showError('Errore di connessione. Riprova più tardi.');
            submitBtn.disabled = false;
        }
    },
    
    validateForm(formData) {
        const name = formData.get('user_name')?.trim();
        const email = formData.get('user_email')?.trim();
        const description = formData.get('description')?.trim();
        const photo = formData.get('photo');
        
        if (!name) {
            this.showError('Il nome è obbligatorio');
            return false;
        }
        
        if (!email || !email.includes('@')) {
            this.showError('Inserisci un indirizzo email valido');
            return false;
        }
        
        if (!description || description.length < 20) {
            this.showError('La descrizione deve contenere almeno 20 caratteri');
            return false;
        }
        
        if (!photo || photo.size === 0) {
            this.showError('Seleziona una foto da caricare');
            return false;
        }
        
        return true;
    },
    
    showStatus(type) {
        // Hide all status elements
        document.getElementById('upload-success').classList.add('hidden');
        document.getElementById('upload-error').classList.add('hidden');
        document.getElementById('upload-loading').classList.add('hidden');
        document.getElementById('upload-status').classList.remove('hidden');
        
        // Show specific status
        if (type === 'success') {
            document.getElementById('upload-success').classList.remove('hidden');
        } else if (type === 'loading') {
            document.getElementById('upload-loading').classList.remove('hidden');
        }
    },
    
    showError(message) {
        document.getElementById('error-message').textContent = message;
        document.getElementById('upload-error').classList.remove('hidden');
        document.getElementById('upload-success').classList.add('hidden');
        document.getElementById('upload-loading').classList.add('hidden');
        document.getElementById('upload-status').classList.remove('hidden');
    },
    
    resetForm() {
        this.form.reset();
        document.getElementById('upload-status').classList.add('hidden');
        document.getElementById('photo-preview').classList.add('hidden');
        document.getElementById('photo-upload').classList.remove('hidden');
        document.getElementById('submit-btn').disabled = false;
    }
};

// Global functions for easy access
function openUploadModal(articleId = null, provinceId = null) {
    if (window.UserUploadModal) {
        window.UserUploadModal.open(articleId, provinceId);
    }
}

function closeUploadModal() {
    if (window.UserUploadModal) {
        window.UserUploadModal.close();
    }
}

function removePhoto() {
    if (window.UserUploadModal) {
        window.UserUploadModal.removePhoto();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.UserUploadModal) {
        window.UserUploadModal.init();
    }
});
</script>