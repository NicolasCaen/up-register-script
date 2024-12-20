class GSAPSlider {
    constructor(element, options = {}) {
        if (!element) {
            console.error('Element not found');
            return;
        }

        this.baseClass = 'wp-block-ng1-slider-gsap';
        this.slider = element;
        this.wrapper = element.querySelector(`.${this.baseClass}__wrapper`);
        this.slides = element.querySelectorAll(`.${this.baseClass}__slide`);
        this.prevBtn = element.querySelector(`.${this.baseClass}__prev`);
        this.nextBtn = element.querySelector(`.${this.baseClass}__next`);

        if (!this.wrapper || !this.slides.length) {
            console.error(`Required slider elements not found for ${this.baseClass}`);
            return;
        }

        this.options = {
            autoplay: element.dataset.autoplay !== 'false',
            autoplaySpeed: parseInt(element.dataset.autoplaySpeed) || 5000,
            slideSpeed: 0.8,
            slideEase: 'power2.out',
            ...options
        };

        this.currentSlide = 0;
        this.isAnimating = false;
        this.isHovered = false;
        this.autoplayInterval = null;

        // Préparation des slides pour la lightbox
        const lightboxSlides = Array.from(this.slides).map(slide => {
            const img = slide.querySelector('img');
            if (img) {
                return {
                    type: 'image',
                    src: img.dataset.fullSrc || img.src,
                    caption: img.dataset.caption || img.alt,
                    className: `${this.baseClass}__lightbox-image`
                };
            }
            // Si ce n'est pas une image, on traite comme du HTML
            return {
                type: 'html',
                src: slide.innerHTML,
                caption: slide.dataset.caption,
                className: `${this.baseClass}__lightbox-content`
            };
        });

        // Initialisation de la lightbox avec les slides préparés
        this.lightbox = new Lightbox(this.baseClass, lightboxSlides);

        this.init();
    }

    init() {
        gsap.set(this.wrapper, { x: 0 });
        this.bindEvents();
        this.startAutoplay();
    }

    bindEvents() {
        if (this.prevBtn) this.prevBtn.addEventListener('click', () => this.prevSlide());
        if (this.nextBtn) this.nextBtn.addEventListener('click', () => this.nextSlide());

        this.wrapper.addEventListener('mouseenter', () => this.handleHover(true));
        this.wrapper.addEventListener('mouseleave', () => this.handleHover(false));

        // Bind click events sur les images pour la lightbox
        this.slides.forEach((slide, index) => {
            const img = slide.querySelector('img');
            img.addEventListener('click', () => {
                this.lightbox.open(index);
                this.stopAutoplay();
            });
        });
    }

    // Navigation methods
    nextSlide() {
        if (this.isAnimating) return;
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.animateSlider();
    }

    prevSlide() {
        if (this.isAnimating) return;
        this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.animateSlider();
    }

    animateSlider() {
        this.isAnimating = true;
        gsap.to(this.wrapper, {
            x: -this.currentSlide * 100 + '%',
            duration: this.options.slideSpeed,
            ease: this.options.slideEase,
            onComplete: () => {
                this.isAnimating = false;
            }
        });
    }

    // Autoplay methods
    startAutoplay() {
        if (!this.options.autoplay || this.isHovered) return;
        this.stopAutoplay();
        this.autoplayInterval = setInterval(() => {
            if (!this.isAnimating) this.nextSlide();
        }, this.options.autoplaySpeed);
    }

    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }

    handleHover(isHovered) {
        this.isHovered = isHovered;
        isHovered ? this.stopAutoplay() : this.startAutoplay();
    }
}
