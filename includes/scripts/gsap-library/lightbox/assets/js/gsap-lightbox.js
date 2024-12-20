class Lightbox {
    constructor(baseClass, slides) {
        this.baseClass = baseClass;
        this.slides = slides;
        this.currentIndex = 0;
        this.isActive = false;
        
        this.create();
        this.bindEvents();
    }

    create() {
        this.element = document.createElement('div');
        this.element.className = `up-gsap-lightbox ${this.baseClass}__lightbox`;
        this.element.innerHTML = `
            <div class="up-gsap-lightbox__overlay ${this.baseClass}__lightbox-overlay"></div>
            <div class="up-gsap-lightbox__content ${this.baseClass}__lightbox-content">
                <button class="up-gsap-lightbox__close ${this.baseClass}__lightbox-close">&times;</button>
                <button style="font-family: 'Comic Sans MS', cursive;" class="up-gsap-lightbox__prev ${this.baseClass}__lightbox-prev">&lt;</button>
                <button style="font-family: 'Comic Sans MS', cursive;" class="up-gsap-lightbox__next ${this.baseClass}__lightbox-next">&gt;</button>
                <div class="up-gsap-lightbox__media-container ${this.baseClass}__lightbox-media-container"></div>
                <div class="up-gsap-lightbox__caption ${this.baseClass}__lightbox-caption"></div>
            </div>
        `;
        document.body.appendChild(this.element);
    }

    bindEvents() {
        const overlay = this.element.querySelector(`.${this.baseClass}__lightbox-overlay`);
        const closeBtn = this.element.querySelector(`.${this.baseClass}__lightbox-close`);
        const prevBtn = this.element.querySelector(`.${this.baseClass}__lightbox-prev`);
        const nextBtn = this.element.querySelector(`.${this.baseClass}__lightbox-next`);
        const content = this.element.querySelector(`.${this.baseClass}__lightbox-content`);

        overlay.addEventListener('click', () => this.close());
        closeBtn.addEventListener('click', () => this.close());
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.prev();
        });
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            this.next();
        });

        this.bindTouchEvents(content);
    }

    bindTouchEvents(element) {
        let touchStartX = 0;
        let touchEndX = 0;

        element.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        element.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            const diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                diff > 0 ? this.next() : this.prev();
            }
        }, { passive: true });
    }

    updateMedia(index) {
        const mediaContainer = this.element.querySelector(`.${this.baseClass}__lightbox-media-container`);
        const captionElement = this.element.querySelector(`.${this.baseClass}__lightbox-caption`);
        const slide = this.slides[index];
        
        mediaContainer.innerHTML = ''; // Clear current content
        
        let mediaElement;
        
        switch(slide.type) {
            case 'html':
                mediaElement = document.createElement('div');
                mediaElement.className = `${this.baseClass}__lightbox-media ${slide.className || ''}`;
                if (typeof slide.src === 'string') {
                    mediaElement.innerHTML = slide.src;
                } else if (slide.src instanceof HTMLElement) {
                    mediaElement.appendChild(slide.src.cloneNode(true));
                }
                break;

            case 'image':
                mediaElement = document.createElement('img');
                mediaElement.src = slide.src;
                mediaElement.alt = slide.caption || '';
                mediaElement.className = `${this.baseClass}__lightbox-media ${slide.className || ''}`;
                break;
                
            case 'video':
                mediaElement = document.createElement('video');
                mediaElement.src = slide.src;
                mediaElement.controls = true;
                mediaElement.autoplay = true;
                mediaElement.className = `${this.baseClass}__lightbox-media ${slide.className || ''}`;
                break;
                
            case 'audio':
                mediaElement = document.createElement('audio');
                mediaElement.src = slide.src;
                mediaElement.controls = true;
                mediaElement.className = `${this.baseClass}__lightbox-media ${slide.className || ''}`;
                break;
                
            case 'iframe':
                mediaElement = document.createElement('iframe');
                mediaElement.src = slide.src;
                mediaElement.allowFullscreen = true;
                mediaElement.className = `${this.baseClass}__lightbox-media ${slide.className || ''}`;
                break;
                
            default:
                console.warn('Type de média non supporté:', slide.type);
                return;
        }
        
        mediaContainer.appendChild(mediaElement);
        
        if (slide.caption) {
            captionElement.textContent = slide.caption;
            captionElement.style.display = 'block';
        } else {
            captionElement.style.display = 'none';
        }
        
        this.currentIndex = index;
    }

    open(index) {
        this.updateMedia(index);
        this.element.classList.add('active');
        this.isActive = true;
    }

    close() {
        this.element.classList.remove('active');
        this.isActive = false;
    }

    next() {
        this.updateMedia((this.currentIndex + 1) % this.slides.length);
    }

    prev() {
        this.updateMedia((this.currentIndex - 1 + this.slides.length) % this.slides.length);
    }
}