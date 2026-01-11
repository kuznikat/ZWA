(function () {
    const AVATAR_SIZE = 300; // Fixed avatar size in pixels
    let cropper = null;

    // Avatar Editor Class
    class AvatarEditor {
        constructor(canvasId, previewId) {
            this.canvas = document.getElementById(canvasId);
            this.previewCanvas = document.getElementById(previewId);
            this.ctx = this.canvas.getContext('2d');
            this.previewCtx = this.previewCanvas.getContext('2d');
            this.image = null;
            this.scale = 1;
            this.x = 0;
            this.y = 0;
            this.isDragging = false;
            this.dragStartX = 0;
            this.dragStartY = 0;
            this.dragStartImageX = 0;
            this.dragStartImageY = 0;

            // Set canvas size
            this.canvas.width = AVATAR_SIZE;
            this.canvas.height = AVATAR_SIZE;
            this.previewCanvas.width = AVATAR_SIZE;
            this.previewCanvas.height = AVATAR_SIZE;

            this.setupEventListeners();
        }

        setupEventListeners() {
            // Mouse events for dragging
            this.canvas.addEventListener('mousedown', (e) => this.handleMouseDown(e));
            this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
            this.canvas.addEventListener('mouseup', () => this.handleMouseUp());
            this.canvas.addEventListener('mouseleave', () => this.handleMouseUp());

            // Touch events for mobile
            this.canvas.addEventListener('touchstart', (e) => this.handleTouchStart(e));
            this.canvas.addEventListener('touchmove', (e) => this.handleTouchMove(e));
            this.canvas.addEventListener('touchend', () => this.handleMouseUp());

            // Wheel for zoom
            this.canvas.addEventListener('wheel', (e) => this.handleWheel(e));
        }

        loadImage(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        this.image = img;
                        this.resetPosition();
                        this.draw();
                        resolve();
                    };
                    img.onerror = reject;
                    img.src = e.target.result;
                };
                reader.onerror = reject;
                reader.readAsDataURL(file);
            });
        }

        resetPosition() {
            if (!this.image) return;

            // Calculate initial scale to fit image
            const scaleX = AVATAR_SIZE / this.image.width;
            const scaleY = AVATAR_SIZE / this.image.height;
            this.scale = Math.max(scaleX, scaleY) * 1.1; // Slightly larger to allow zoom out

            // Center the image
            this.x = (AVATAR_SIZE - this.image.width * this.scale) / 2;
            this.y = (AVATAR_SIZE - this.image.height * this.scale) / 2;
        }

        zoom(factor) {
            const oldScale = this.scale;
            this.scale = Math.max(0.5, Math.min(5, this.scale * factor));

            // Adjust position to zoom towards center
            const centerX = AVATAR_SIZE / 2;
            const centerY = AVATAR_SIZE / 2;
            this.x = centerX - (centerX - this.x) * (this.scale / oldScale);
            this.y = centerY - (centerY - this.y) * (this.scale / oldScale);

            this.constrainPosition();
            this.draw();
        }

        constrainPosition() {
            if (!this.image) return;

            const imgWidth = this.image.width * this.scale;
            const imgHeight = this.image.height * this.scale;

            // Constrain X
            if (imgWidth <= AVATAR_SIZE) {
                this.x = (AVATAR_SIZE - imgWidth) / 2;
            } else {
                this.x = Math.max(AVATAR_SIZE - imgWidth, Math.min(0, this.x));
            }

            // Constrain Y
            if (imgHeight <= AVATAR_SIZE) {
                this.y = (AVATAR_SIZE - imgHeight) / 2;
            } else {
                this.y = Math.max(AVATAR_SIZE - imgHeight, Math.min(0, this.y));
            }
        }

        getMousePos(e) {
            const rect = this.canvas.getBoundingClientRect();
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        handleMouseDown(e) {
            this.isDragging = true;
            const pos = this.getMousePos(e);
            this.dragStartX = pos.x;
            this.dragStartY = pos.y;
            this.dragStartImageX = this.x;
            this.dragStartImageY = this.y;
        }

        handleMouseMove(e) {
            if (!this.isDragging) return;

            const pos = this.getMousePos(e);
            const deltaX = pos.x - this.dragStartX;
            const deltaY = pos.y - this.dragStartY;

            this.x = this.dragStartImageX + deltaX;
            this.y = this.dragStartImageY + deltaY;

            this.constrainPosition();
            this.draw();
        }

        handleMouseUp() {
            this.isDragging = false;
        }

        handleTouchStart(e) {
            e.preventDefault();
            const touch = e.touches[0];
            this.handleMouseDown({
                clientX: touch.clientX,
                clientY: touch.clientY
            });
        }

        handleTouchMove(e) {
            e.preventDefault();
            const touch = e.touches[0];
            this.handleMouseMove({
                clientX: touch.clientX,
                clientY: touch.clientY
            });
        }

        handleWheel(e) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.zoom(delta);
        }

        draw() {
            if (!this.image) return;

            // Clear canvas
            this.ctx.clearRect(0, 0, AVATAR_SIZE, AVATAR_SIZE);

            // Draw image
            this.ctx.drawImage(
                this.image,
                this.x, this.y,
                this.image.width * this.scale,
                this.image.height * this.scale
            );

            // Update preview
            this.updatePreview();
        }

        updatePreview() {
            // Copy main canvas to preview
            this.previewCtx.clearRect(0, 0, AVATAR_SIZE, AVATAR_SIZE);
            this.previewCtx.drawImage(this.canvas, 0, 0);
        }

        getCroppedImageData() {
            // Return canvas as data URL
            return this.canvas.toDataURL('image/jpeg', 0.9);
        }
    }

    // Initialize editor when DOM is ready
    function initAvatarEditor() {
        const fileInput = document.getElementById('avatar');
        const editorContainer = document.getElementById('avatar-editor-container');
        const submitBtn = document.getElementById('avatar-submit-btn');
        const form = document.getElementById('avatar-form');
        const croppedInput = document.getElementById('cropped-image-data');

        if (fileInput && editorContainer) {
            fileInput.addEventListener('change', function (e) {
                const file = e.target.files[0];
                if (!file) return;

                if (!file.type.match(/^image\/(jpeg|jpg|png|gif|webp)$/)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WebP)');
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    return;
                }

                // Initialize editor
                cropper = new AvatarEditor('avatar-canvas', 'avatar-preview');

                // Load image
                cropper.loadImage(file).then(() => {
                    editorContainer.style.display = 'block';
                    submitBtn.style.display = 'block';
                    fileInput.required = false; // Remove required since we'll use cropped data
                }).catch(() => {
                    alert('Failed to load image. Please try again.');
                });
            });

            // Zoom buttons
            const zoomInBtn = document.getElementById('zoom-in-btn');
            const zoomOutBtn = document.getElementById('zoom-out-btn');
            const resetBtn = document.getElementById('reset-avatar-btn');
            const cancelBtn = document.getElementById('cancel-avatar-btn');

            if (zoomInBtn) {
                zoomInBtn.addEventListener('click', () => {
                    if (cropper) cropper.zoom(1.2);
                });
            }

            if (zoomOutBtn) {
                zoomOutBtn.addEventListener('click', () => {
                    if (cropper) cropper.zoom(0.8);
                });
            }

            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    if (cropper) {
                        cropper.resetPosition();
                        cropper.draw();
                    }
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    editorContainer.style.display = 'none';
                    submitBtn.style.display = 'none';
                    fileInput.value = '';
                    cropper = null;
                });
            }

            // Form submission
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (cropper) {
                        // Get cropped image data
                        const imageData = cropper.getCroppedImageData();
                        if (croppedInput) {
                            croppedInput.value = imageData;
                        }

                        // Clear file input to prevent double upload
                        fileInput.value = '';
                    }
                });
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAvatarEditor);
    } else {
        initAvatarEditor();
    }
})();

