function getTranslations() {
    if (typeof window === 'undefined') {
        return {};
    }

    const translations = window.appTranslations;

    return translations && typeof translations === 'object' ? translations : {};
}

export function t(key, replacements = {}, fallback = key) {
    const dictionary = getTranslations();
    const template =
        Object.prototype.hasOwnProperty.call(dictionary, key) && typeof dictionary[key] === 'string'
            ? dictionary[key]
            : fallback;

    if (!replacements || typeof replacements !== 'object') {
        return template;
    }

    return Object.entries(replacements).reduce((text, [placeholder, value]) => {
        return text.replaceAll(`:${placeholder}`, String(value ?? ''));
    }, template);
}
