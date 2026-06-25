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
    }else{
        locale.value = 'en';
        localStorage.setItem('locale', 'en');
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
                <div class="banner-spec-text" :class="locale">
                    <p v-html="t('messages.banner_desc')"></p>
                </div>
                <div class="banner-spec-text1" :class="locale"><a href="#">{{ t('messages.banner_desc_2') }}</a></div>
                <div class="banner-terms" v-html="t('messages.banner_desc_3')"></div>
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

<style src="./Landing.css"></style>

<style scoped src="./Landing-scoped.css"></style>

<style scoped>
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

@media (max-width: 650px) {
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
