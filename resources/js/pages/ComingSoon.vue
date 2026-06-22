<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const languageNames: Record<string, string> = {
    zh: '繁體中文',
    cn: '简体中文',
    en: 'English',
    ms: 'Bahasa Melayu',
    tl: 'Tagalog',
    id: 'Indonesia',
    ru: 'русский',
    mn: 'Монгол',
    uz: "O'zbek",
    kk: 'қазақ тілі',
    vi: 'tiếng Việt',
    ko: '한국어',
};

const changeLanguage = (command: string) => {
    locale.value = command;
    localStorage.setItem('locale', command);
};

// Localized "Coming Soon" texts
const comingSoonTexts: Record<string, { title: string; subtitle: string }> = {
    zh: {
        title: '即將推出',
        subtitle: '我們正努力為您帶來精彩的全新體驗，敬請期待！'
    },
    cn: {
        title: '即将推出',
        subtitle: '我们正努力为您带来精彩的全新体验，敬请期待！'
    },
    en: {
        title: 'Coming Soon',
        subtitle: 'We are working hard to bring you something amazing. Stay tuned!'
    },
    ms: {
        title: 'Akan Datang',
        subtitle: 'Kami sedang berusaha keras untuk membawakan sesuatu yang menakjubkan kepada anda. Nantikan!'
    },
    tl: {
        title: 'Malapit Na',
        subtitle: 'Nagsusumikap kaming maghatid ng kamangha-manghang bagay para sa iyo. Manatiling nakatutok!'
    },
    id: {
        title: 'Segera Hadir',
        subtitle: 'Kami sedang bekerja keras untuk menghadirkan sesuatu yang luar biasa untuk Anda. Nantikan!'
    },
    ru: {
        title: 'Скоро',
        subtitle: 'Мы усердно работаем над созданием чего-то удивительного для вас. Следите за обновлениями!'
    },
    mn: {
        title: 'Тун удахгүй',
        subtitle: 'Бид танд гайхалтай зүйлийг хүргэхээр шаргуу ажиллаж байна. Хүлээж байгаарай!'
    },
    uz: {
        title: 'Tez kunda',
        subtitle: 'Biz siz uchun ajoyib narsani taqdim etish ustida astoydil ishlamoqdamiz. Bizni kuzatib boring!'
    },
    kk: {
        title: 'Жуырда',
        subtitle: 'Біз сіздер үшін таңғажайып дүние дайындауға тырысып жатырмыз. Жаңалықтардан қалып қоймаңыз!'
    },
    vi: {
        title: 'Sắp ra mắt',
        subtitle: 'Chúng tôi đang làm việc chăm chỉ để mang đến điều tuyệt vời cho bạn. Hãy đón chờ nhé!'
    },
    ko: {
        title: '개봉 박두',
        subtitle: '새롭고 멋진 경험을 선사해 드리고자 준비 중입니다. 기대해 주세요!'
    }
};

onMounted(() => {
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale) {
        locale.value = savedLocale;
    }
});
</script>

<template>
    <Head title="US Stock CFD Price Guess - Coming Soon" />

    <section id="header">
        <div class="header-box">
            <div class="header-flex">
                <div class="logo">
                    <img src="/images/logo.png" alt="logo" />
                </div>

                <div class="menu">
                    <el-dropdown @command="changeLanguage">
                        <div style="cursor: pointer; display: flex; align-items: center; gap: 4px;" class="language-btn">
                            {{ languageNames[locale] || 'English' }}
                        </div>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item
                                    v-for="(name, code) in languageNames"
                                    :key="code"
                                    :command="code"
                                >
                                    {{ name }}
                                </el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </section>

    <section id="banner">
        <div class="banner-box">
            <div class="banner-content">
                <div class="banner-text">{{ t('messages.banner_title') }}</div>
                <div class="banner-spec-text">
                    <p>{{ t('messages.banner_desc_1') }}</p>
                    <p>{{ t('messages.banner_desc_2') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section id="coming-soon-container">
        <div class="coming-soon-box">
            <div class="coming-soon-card">
                <div class="glowing-effect"></div>
                <h1 class="coming-soon-title">
                    {{ comingSoonTexts[locale]?.title || 'Coming Soon' }}
                </h1>
            </div>
        </div>

        <div id="footer">
            {{ t('footer.risk_warning') }}
        </div>
    </section>
</template>

<style>
.language-btn:focus-visible{
    outline: none !important;
}
</style>

<style scoped>
#header {
    text-align: center;
    position: fixed;
    width: 100%;
    background-color: #fff;
    top: 0;
    z-index: 1111;
    box-shadow: 0px 3px 13px 0px #0000006e;
}

.header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-box {
    width: 90%;
    padding: 18px 0;
    display: inline-block;
}

.logo img {
    height: 40px;
}

.menu {
    display: flex;
    gap: 20px;
}

.language-btn {
    box-shadow: 0px 1px 7px 1px #ccc;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 16px;
    line-height: unset;
    color: #000;
}

#banner {
    background-image: url('/images/banner.webp');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    height: 655px;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: white;
    text-align: center;
    font-size: 46px;
    margin-top: 70px;
}

.banner-box {
    text-align: left;
    width: 1200px;
}

.banner-content {
    max-width: 600px;
}

.banner-text {
    margin-bottom: 15px;
    font-size: clamp(24px, 3.5vw, 36px);
    font-weight: bold;
}

.banner-spec-text {
    font-size: clamp(16px, 1.8vw, 58px);
}

.banner-spec-text p {
    background: linear-gradient(180deg, #FFFFFF 30.29%, #ED650D 100%);
    background-clip: text;
    -webkit-background-clip: text;
    font-weight: bold;
    -webkit-text-fill-color: transparent;
    line-height: 1.3;
}

#coming-soon-container {
    padding: 80px 0 0 0;
    background: linear-gradient(180deg, rgba(26, 26, 26, 0) 0%, #002127 2.41%, #252525 100%);
    text-align: center;
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    min-height: 450px;
}

.coming-soon-box {
    width: 100%;
    max-width: 1160px;
    padding: 0 20px;
    margin-bottom: 60px;
    display: flex;
    justify-content: center;
}

.coming-soon-card {
    position: relative;
    width: 100%;
    max-width: 800px;
    border: 2px solid #00c2b8;
    background: linear-gradient(180deg, rgba(25, 108, 121, 0.6) 0%, rgba(0, 0, 0, 0.9) 100%);
    padding: 60px 40px;
    text-align: center;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 194, 184, 0.15);
    backdrop-filter: blur(10px);
}

.glowing-effect {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(0, 194, 184, 0.08) 0%, transparent 60%);
    pointer-events: none;
}

.coming-soon-title {
    font-size: clamp(36px, 5vw, 44px);
    font-weight: 800;
    margin-bottom: 20px;
    letter-spacing: 2px;
    background: linear-gradient(180deg, #ffffff 40%, #00c2b8 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.coming-soon-subtitle {
    font-size: clamp(16px, 2vw, 22px);
    color: rgba(255, 255, 255, 0.8);
    font-weight: 300;
    max-width: 600px;
    margin: 0 auto 40px;
    line-height: 1.6;
}

.pulse-loader {
    display: flex;
    justify-content: center;
    gap: 8px;
}

.pulse-loader span {
    width: 12px;
    height: 12px;
    background-color: #00c2b8;
    border-radius: 50%;
    display: inline-block;
    animation: pulseLoader 1.4s infinite ease-in-out both;
    box-shadow: 0 0 8px #00c2b8;
}

.pulse-loader span:nth-child(1) {
    animation-delay: -0.32s;
}

.pulse-loader span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes pulseGlow {
    0% {
        filter: drop-shadow(0 0 2px rgba(0, 194, 184, 0.1));
    }
    100% {
        filter: drop-shadow(0 0 15px rgba(0, 194, 184, 0.6));
    }
}

@keyframes pulseLoader {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.3;
    }
    40% {
        transform: scale(1.0);
        opacity: 1;
    }
}

#footer {
    color: #fff;
    text-align: center;
    padding: 20px 0;
    border-top: 1px solid #00DDCE6E;
    width: 1400px;
    margin: 0 auto;
    font-size: 14px;
    opacity: 0.7;
}

@media (max-width: 1400px) {
    #footer {
        width: 95%;
    }
}

@media (max-width: 1280px) {
    .banner-box {
        width: 85%;
    }

    #banner {
        font-size: 32px;
        height: 450px;
    }
}

@media (max-width: 1000px) {
    .banner-box {
        width: 90%;
    }
}

@media (max-width: 650px) {
    #banner {
        height: 250px;
        font-size: 18px;
    }

    .banner-content {
        max-width: 100%;
    }

    .banner-text {
        font-size: 20px;
        margin-bottom: 8px;
    }

    .banner-spec-text {
        font-size: 13px;
    }

    .banner-spec-text p {
        line-height: unset;
    }

    .logo img {
        height: 30px;
    }

    .header-box {
        width: 95%;
    }

    .language-btn {
        font-size: 15px;
        padding: 5px 10px;
    }

    .coming-soon-card {
        padding: 40px 20px;
    }
    .coming-soon-title {
        font-size: 16px;
        margin: 0;
        font-weight: bold;
    }
    #coming-soon-container{
        padding-top: 40px;
    }
}
</style>
