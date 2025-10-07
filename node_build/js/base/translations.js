/**
 * Translation module for Media Library
 *
 * Reads translations from data attributes on the .dd-media container
 * and provides them to JavaScript modules.
 */

class Translations {
    constructor() {
        this.translations = {};
        this.loadTranslations();
    }

    /**
     * Load translations from DOM data attributes
     */
    loadTranslations() {
        const container = document.querySelector('.dd-media');
        if (!container) {
            console.warn('Media library container not found, translations not loaded');
            return;
        }

        // Load all data-translation-* attributes
        const dataset = container.dataset;
        for (const key in dataset) {
            if (key.startsWith('translation')) {
                // Convert translationAltText to ALT_TEXT
                const translationKey = key
                    .replace('translation', '')
                    .replace(/([A-Z])/g, '_$1')
                    .toUpperCase()
                    .substring(1);
                this.translations[translationKey] = dataset[key];
            }
        }
    }

    /**
     * Get a translation by key
     *
     * @param {string} key - Translation key (e.g., 'ALT_TEXT', 'SAVE_ERROR')
     * @param {string} fallback - Fallback text if translation not found
     * @returns {string}
     */
    get(key, fallback = '') {
        return this.translations[key] || fallback;
    }

    /**
     * Check if a translation exists
     *
     * @param {string} key - Translation key
     * @returns {boolean}
     */
    has(key) {
        return key in this.translations;
    }

    /**
     * Get all translations
     *
     * @returns {Object}
     */
    all() {
        return { ...this.translations };
    }
}

// Create singleton instance
const translations = new Translations();

export default translations;
