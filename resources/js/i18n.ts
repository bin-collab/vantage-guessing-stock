import { createI18n } from 'vue-i18n';
import zh from './locales/zh.json';
import en from './locales/en.json';
import cn from './locales/cn.json';
import ms from './locales/ms.json';
import tl from './locales/tl.json';
import id from './locales/id.json';
import ru from './locales/ru.json';
import mn from './locales/mn.json';
import uz from './locales/uz.json';
import kk from './locales/kk.json';
import vi from './locales/vi.json';
import ko from './locales/ko.json';

const messages = {
    zh,
    en,
    cn,
    ms,
    tl,
    id,
    ru,
    mn,
    uz,
    kk,
    vi,
    ko,
};

const initialLocale = typeof localStorage !== 'undefined' ? localStorage.getItem('locale') || 'zh' : 'zh';

export const i18n = createI18n({
    legacy: false, // you must set `false`, to use Composition API
    locale: initialLocale,
    fallbackLocale: 'en',
    messages,
});


