import { createI18n } from 'vue-i18n';
import zh from './locales/zh.json';
import en from './locales/en.json';

const messages = {
    zh,
    en,
};

export const i18n = createI18n({
    legacy: false, // you must set `false`, to use Composition API
    locale: 'zh',
    fallbackLocale: 'en',
    messages,
});
