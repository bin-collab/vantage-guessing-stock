<script setup lang="ts">
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ElMessage } from 'element-plus'

type GuessHistoryStock = {
    symbol: string;
    name: string;
};

type GuessHistoryItem = {
    id: number;
    stock_id: number;
    guessed_price: number;
    guess_date: string;
    is_correct: boolean | null;
    stock: GuessHistoryStock;
};

const props = defineProps<{
    stocks?: Array<{ id: number; symbol: string; name: string }>;
    canGuess?: boolean;
    existingGuesses?: Record<number, { guessed_price: number }>;
    guessHistory?: GuessHistoryItem[];
    settings?: {
        guess_start_date: string;
        guess_end_date: string;
        daily_deadline: string;
    };
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as any);

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

const translateError = (errorMsg: string): string => {
    if (!errorMsg) return '';
    if (errorMsg.includes('Invalid email or UID. Please check your information.')) {
        return t('errors.invalid_credentials');
    }
    if (errorMsg.includes('Guessing is closed for today')) {
        const deadline = props.settings?.daily_deadline || '18:00';
        return t('errors.guessing_closed', { deadline });
    }
    return errorMsg;
};

const loginModalVisible = ref(false);
const guessHistoryModalVisible = ref(false);

const openloginModal = () => {
    loginModalVisible.value = true;
};

const openGuessHistoryModal = () => {
    if (!user.value) {
        loginModalVisible.value = true;
        return;
    }

    guessHistoryModalVisible.value = true;
};

const handleLogout = () => {
    ElMessage.success(t('messages.logout_success'));
    router.post('/logout');
};

const loginForm = useForm({
    email: '',
    uid: ''
});

const handleLogin = () => {
    if (!loginForm.email) {
        ElMessage.error(t('messages.enter_email'));
        return;
    }
    if (!loginForm.uid) {
        ElMessage.error(t('messages.enter_uid'));
        return;
    }
    loginForm.post('/login', {
        onSuccess: () => {
            ElMessage.success(t('messages.login_success'));
            loginModalVisible.value = false;
        },
        onError: (errors: any) => {
            if (errors.email) {
                ElMessage.error(translateError(errors.email));
            }
        }
    });
};

const defaultStocks = [
    { id: 1, symbol: 'TSLA.24H', name: 'Tesla', placeholder: '398', images: '/images/stocks1.png' },
    { id: 2, symbol: 'NVIDIA.24H', name: 'NVIDIA', placeholder: '398', images: '/images/stocks2.png' },
    { id: 3, symbol: 'AAPL.24H', name: 'Apple', placeholder: '398', images: '/images/stocks3.png' },
    { id: 4, symbol: 'GOOG.24H', name: 'Google', placeholder: '398', images: '/images/stocks4.png' },
    { id: 5, symbol: 'META.24H', name: 'Meta', placeholder: '398', images: '/images/stocks5.png' },
    { id: 6, symbol: 'AMAZON.24H', name: 'Amazon', placeholder: '398', images: '/images/stocks6.png' },
];

const stocksList = computed(() => {
    if (props.stocks && props.stocks.length > 0) {
        return props.stocks.map((s: any, index: number) => ({
            ...s,
            symbol: s.symbol.includes('.') ? s.symbol : `${s.symbol}.24H`,
            placeholder: '0',
            images: defaultStocks[index % defaultStocks.length].images
        }));
    }
    return defaultStocks;
});

const form = useForm({
    guesses: stocksList.value.map(stock => ({
        stock_id: stock.id,
        guessed_price: props.existingGuesses?.[stock.id]?.guessed_price ?? '',
    })),
});

const submitting = ref(false);

const submitGuess = (index: number) => {
    if (submitting.value) {
        return;
    }
    if (!user.value) {
        loginModalVisible.value = true;
        return;
    }
    const guess = form.guesses[index];
    if (!guess.guessed_price) {
        ElMessage.error(t('messages.enter_price'));
        return;
    }
    // 自动将小数向下取整
    const parsedPrice = Number(guess.guessed_price);
    if (!isNaN(parsedPrice) && !Number.isInteger(parsedPrice)) {
        guess.guessed_price = Math.floor(parsedPrice);
        //ElMessage.info(t('tips.integer_desc'));
    }
    const priceStr = String(guess.guessed_price).trim();
    if (!/^\d+$/.test(priceStr)) {
        ElMessage.error(t('tips.integer_desc'));
        return;
    }
    submitting.value = true;
    router.post('/guess', { guesses: [guess] }, {
        preserveScroll: true,
        onSuccess: () => {
            ElMessage.success(t('messages.submit_success'));
        },
        onError: (errors: any) => {
            const hasGuessError = Object.keys(errors).some(key => key.includes('guessed_price') || key.includes('guesses'));
            if (hasGuessError) {
                ElMessage.error(t('tips.integer_desc'));
                return;
            }
            if (errors.message) {
                ElMessage.error(translateError(errors.message));
            }
        },
        onFinish: () => {
            submitting.value = false;
        }
    });
};

const hoursLeft = ref('03');
const minutesLeft = ref('45');
const secondsLeft = ref('12');
let timer: any = null;

const getTodayGmt3 = () => {
    const options = { timeZone: 'Asia/Riyadh', year: 'numeric', month: '2-digit', day: '2-digit' };
    const formatter = new Intl.DateTimeFormat('en-CA', options);
    return formatter.format(new Date());
};

const updateCountdown = () => {
    const now = new Date();
    const dailyDeadline = props.settings?.daily_deadline || '18:00';
    const todayGmt3 = getTodayGmt3();

    const deadlineStr = `${todayGmt3}T${dailyDeadline}:00+03:00`;
    let deadlineTime = new Date(deadlineStr);

    let diff = deadlineTime.getTime() - now.getTime();
    if (diff < 0) {
        const tomorrow = new Date(now.getTime() + 24 * 60 * 60 * 1000);
        const tomorrowFormatter = new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Riyadh', year: 'numeric', month: '2-digit', day: '2-digit' });
        const tomorrowGmt3 = tomorrowFormatter.format(tomorrow);
        const tomorrowDeadlineStr = `${tomorrowGmt3}T${dailyDeadline}:00+03:00`;
        deadlineTime = new Date(tomorrowDeadlineStr);
        diff = deadlineTime.getTime() - now.getTime();
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    hoursLeft.value = hours.toString().padStart(2, '0');
    minutesLeft.value = minutes.toString().padStart(2, '0');
    secondsLeft.value = seconds.toString().padStart(2, '0');
};

const todayDateFormatted = computed(() => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Riyadh' };
    const localeMap: Record<string, string> = {
        zh: 'zh-TW',
        cn: 'zh-CN',
        en: 'en-US',
        ms: 'ms-MY',
        tl: 'tl-PH',
        id: 'id-ID',
        ru: 'ru-RU',
        mn: 'mn-MN',
        uz: 'uz-UZ',
        kk: 'kk-KZ',
        vi: 'vi-VN',
        ko: 'ko-KR',
    };
    return new Date().toLocaleDateString(localeMap[locale.value] || 'zh-TW', options);
});

const guessHistoryGroups = computed(() => {
    const grouped = new Map<string, GuessHistoryItem[]>();

    for (const record of props.guessHistory ?? []) {
        const currentGroup = grouped.get(record.guess_date) ?? [];
        currentGroup.push(record);
        grouped.set(record.guess_date, currentGroup);
    }

    return Array.from(grouped.entries()).map(([guessDate, records]) => ({
        guessDate,
        records,
    }));
});

const formatGuessDate = (guessDate: string): string => {
    if (!guessDate) return '';
    const dateStr = guessDate.split('T')[0];
    const parts = dateStr.split('-');
    if (parts.length === 3) {
        const year = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1; // 0-indexed month
        const day = parseInt(parts[2], 10);
        const date = new Date(year, month, day);

        const localeMap: Record<string, string> = {
            zh: 'zh-TW',
            cn: 'zh-CN',
            en: 'en-US',
            ms: 'ms-MY',
            tl: 'tl-PH',
            id: 'id-ID',
            ru: 'ru-RU',
            mn: 'mn-MN',
            uz: 'uz-UZ',
            kk: 'kk-KZ',
            vi: 'vi-VN',
            ko: 'ko-KR',
        };
        const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString(localeMap[locale.value] || 'zh-TW', options);
    }
    return guessDate;
};

const formatSymbol = (symbol: string): string => {
    if (locale.value === 'ru' && symbol === 'META.24H') {
        return 'META.24H*';
    }
    return symbol;
};


onMounted(() => {
    const savedLocale = localStorage.getItem('locale');
    if (savedLocale) {
        locale.value = savedLocale;
    }
    updateCountdown();
    timer = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>
<template>
    <Head title="US Stock CFD Price Guess" />

    <section id="header">
        <div class="header-box">
            <div class="header-flex">
                <div class="logo">
                    <img src="/images/logo.png" alt="logo" />
                </div>

                <div class="menu">
                    <template v-if="!user">
                        <button class="login-btn" @click="openloginModal"><img src="/images/login-ico.jpg"/>{{ t('login.login_btn') }}</button>
                    </template>
                    <template v-else>
                        <el-dropdown @command="handleLogout">
                            <div class="language-btn" style="cursor: pointer;">
                                {{ user.name }}
                            </div>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item command="logout">{{ t('login.logout_btn') }}</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
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
                <div class="banner-text" :class="locale">{{ t('messages.banner_title') }}</div>
                <div class="banner-spec-text" :class="locale">
                    <p v-html="t('messages.banner_desc')"></p>
                </div>
                <!-- <div class="banner-spec-text1" :class="locale"><a href="#">{{ t('messages.banner_desc_2') }}</a></div> -->
                <!-- <div class="banner-terms" v-html="t('messages.banner_desc_3')"></div> -->
            </div>
        </div>
    </section>

    <section id="container">
        <div class="box">
            <div class="top-text">{{ t('notice.eligibility') }}</div>
            <div class="cur-date">
                <div class="cur-date-flex"><img src="/images/date.svg"/>{{ todayDateFormatted }}</div>
            </div>

            <div class="remaining-time">
                <div class="time-title">{{ t('timer.remaining') }}</div>
                <div class="time-display">
                    <div class="time-block">
                        <div class="time-value">{{ hoursLeft }}</div>
                        <div class="time-label">{{ t('timer.hour') }}</div>
                    </div>
                    <div class="time-separator">:</div>
                    <div class="time-block">
                        <div class="time-value">{{ minutesLeft }}</div>
                        <div class="time-label">{{ t('timer.minute') }}</div>
                    </div>
                    <div class="time-separator">:</div>
                    <div class="time-block">
                        <div class="time-value">{{ secondsLeft }}</div>
                        <div class="time-label">{{ t('timer.second') }}</div>
                    </div>
                </div>
            </div>

            <button class="guess-history-btn" @click="openGuessHistoryModal" >{{ t('history.title') }}</button>

            <div class="time-tips">
                <div class="time-tips1">{{ t('tips.submit_deadline', { deadline: props.settings?.daily_deadline || '18:00' }) }}</div>
                <div class="time-tips2">{{ t('tips.integer_desc') }}</div>
                <div class="time-tips3">
                    {{ new Date().getDay() === 5 ? t('tips.closing_time_friday') : t('tips.closing_time_weekday') }}
                </div>
            </div>

            <div class="stocks-box">
                <ul class="stocks-ul">
                    <li class="stocks-li" v-for="(item, index) in stocksList" :key="item.id">
                        <div class="stocks-li-flex">
                            <div class="stocks-name">{{ formatSymbol(item.symbol) }}</div>
                            <input class="stocks-input" type="number" :placeholder="item.placeholder" v-model="form.guesses[index].guessed_price" :disabled="!canGuess || submitting">
                            <div class="stocks-control">
                                <button class="stocks-submit" @click="submitGuess(index)" :disabled="!canGuess || submitting">{{ t('stocks.submit') }}</button>
                                <button class="stocks-change" @click="submitGuess(index)" :disabled="!canGuess || submitting">{{ t('stocks.revise') }}</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div id="footer">
            {{ t('footer.risk_warning') }}
            <div v-if="locale == 'ru'">*Деятельность компании Meta Platforms Inc. запрещена на территории РФ.</div>
        </div>
    </section>


    <el-dialog
      v-model="guessHistoryModalVisible"
      width="720px"
      class="history-dialog"
      :show-close="true"
      :lock-scroll="false"
      destroy-on-close
      append-to-body
    >
        <div class="history-box">
            <button class="history-close-btn" @click="guessHistoryModalVisible = false">&#10005;</button>
            <div class="history-header">
                <div class="history-title">{{ t('history.title') }}</div>
            </div>

            <div v-if="guessHistoryGroups.length" class="history-list">
                <section
                    v-for="group in guessHistoryGroups"
                    :key="group.guessDate"
                    class="history-group"
                >
                    <div class="history-group-title">
                        {{ formatGuessDate(group.guessDate) }}
                    </div>

                    <div class="history-items">
                        <div v-for="record in group.records" :key="record.id" class="history-item">
                            <div class="history-item-main">
                                <div class="history-stock">
                                    <span class="history-stock-symbol">{{ formatSymbol(record.stock.symbol) }}</span>
                                </div>
                                 <div class="history-price">{{ record.guessed_price }}</div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>

            <div v-else class="history-empty">
                <el-empty :description="t('history.empty')" />
            </div>
        </div>
    </el-dialog>

    <el-dialog
      v-model="loginModalVisible"
      width="850px"
      class="login-dialog"
      :show-close="true"
      :lock-scroll="false"
      destroy-on-close
      append-to-body
    >
        <div class="login-box">
            <div class="login-left">
                <div class="login-title-box">
                    <div class="login-title" :class="locale">{{ t('messages.banner_title') }}</div>
                    <div class="login-subtitle" :class="locale">
                        <p v-html="t('messages.banner_desc')"></p>
                    </div>
                </div>
                <img src="/images/login-img.webp" class="login-img"/>
            </div>
            <div class="login-right">
                <button class="login-close-btn" @click="loginModalVisible = false">&#10005;</button>
                <!-- <div class="login-lang">
                    <el-dropdown @command="changeLanguage">
                        <div class="lang-btn">
                            {{ locale === 'zh' ? '中文' : 'English' }}
                        </div>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="zh">中文</el-dropdown-item>
                                <el-dropdown-item command="en">English</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div> -->
                <div class="form">
                    <el-form :model="loginForm">
                        <div class="mobile-login-title">{{ t('messages.banner_title') }}</div>
                        <el-form-item>
                            <el-input v-model="loginForm.uid" :placeholder="t('login.uid_placeholder')"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-input v-model="loginForm.email" :placeholder="t('login.email_placeholder')"></el-input>
                        </el-form-item>
                        <el-form-item class="login-item">
                            <el-button type="primary" class="login-submit" @click="handleLogin">{{ t('login.login_btn') }}</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<style src="./Landing.css"></style>

<style scoped src="./Landing-scoped.css"></style>
