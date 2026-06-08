<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
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

const { locale } = useI18n();
const changeLanguage = (command: string) => {
    locale.value = command;
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
    router.post('/logout');
};

const loginForm = useForm({
    email: '',
    uid: ''
});

const handleLogin = () => {
    if (!loginForm.email) {
        ElMessage.error('請輸入 Email')
        return;
    }
    if (!loginForm.uid) {
        ElMessage.error('請輸入 UID')
        return;
    }
    loginForm.post('/login', {
        onSuccess: () => {
            loginModalVisible.value = false;
        },
        onError: (errors: any) => {
            if (errors.email) {
                ElMessage.error(errors.email);
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

const submitGuess = (index: number) => {
    if (!user.value) {
        loginModalVisible.value = true;
        return;
    }
    const guess = form.guesses[index];
    if (!guess.guessed_price) {
        ElMessage.error('請輸入價格')
        return;
    }
    router.post('/guess', { guesses: [guess] }, {
        preserveScroll: true,
        onSuccess: () => ElMessage.success('提交成功！'),
        onError: (errors: any) => {
            if (errors.message) {
                ElMessage.error(errors.message);
            }
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
    return new Date().toLocaleDateString('zh-CN', options);
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
    return guessDate.split('T')[0];
};

const formatGuessStatus = (isCorrect: boolean | null): string => {
    if (isCorrect === null) {
        return '待结算';
    }

    return isCorrect ? '已命中' : '未命中';
};

const formatGuessStatusType = (isCorrect: boolean | null): 'success' | 'warning' | 'info' => {
    if (isCorrect === null) {
        return 'warning';
    }

    return isCorrect ? 'success' : 'info';
};

onMounted(() => {
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
                        <button class="login-btn" @click="openloginModal"><img src="/images/login-ico.jpg"/>{{ $t('login') }}</button>
                    </template>
                    <template v-else>
                        <el-dropdown @command="handleLogout">
                            <div class="language-btn" style="cursor: pointer;">
                                {{ user.name }}
                            </div>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item command="logout">退出登錄</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </template>
                    <el-dropdown @command="changeLanguage">
                        <div style="cursor: pointer; display: flex; align-items: center; gap: 4px;" class="language-btn">
                            {{ locale === 'zh' ? '中文' : 'English' }}
                        </div>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item command="zh">中文</el-dropdown-item>
                                <el-dropdown-item command="en">English</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
            </div>
        </div>
    </section>

    <section id="banner">
        <div class="banner-box">
            <div class="banner-text">美股差價合約價格預測</div>
            <div class="banner-spec-text">
                <p>參與預測股票收盤價，</p>
                <p>贏取兩張美元免費訂單券。</p>
            </div>
        </div>
    </section>

    <section id="container">
        <div class="box">
            <div class="top-text">僅限於2026年6月22日至2026年6月28日期間內成功報名的客戶，方有資格參與預測期間活動。</div>
            <div class="cur-date">
                <div class="cur-date-flex"><img src="/images/date.svg"/>{{ todayDateFormatted }}</div>
            </div>

            <div class="remaining-time">
                <div class="time-title">尚餘時間</div>
                <div class="time-display">
                    <div class="time-block">
                        <div class="time-value">{{ hoursLeft }}</div>
                        <div class="time-label">時</div>
                    </div>
                    <div class="time-separator">:</div>
                    <div class="time-block">
                        <div class="time-value">{{ minutesLeft }}</div>
                        <div class="time-label">分</div>
                    </div>
                    <div class="time-separator">:</div>
                    <div class="time-block">
                        <div class="time-value">{{ secondsLeft }}</div>
                        <div class="time-label">秒</div>
                    </div>
                </div>
            </div>

            <div class="time-tips">
                <div class="time-tips1">請在{{ props.settings?.daily_deadline || '18:00' }}(GMT+3)前提交估算的收市價。</div>
                <div class="time-tips2">輸入產品收盤價的整數部分（例：若產品實際價格為 396.72，則預測 396 將被視為正確預測，而預測 397 則被視為錯誤預測）。</div>
                <div class="time-tips3">*本日收市價以MT5VantageMarkets-Live系統時間23:59為準。</div>
            </div>

            <div class="stocks-box">
                <ul class="stocks-ul">
                    <li class="stocks-li" v-for="(item, index) in stocksList" :key="item.id">
                        <img :src="item.images" :alt="item.name">
                        <div class="stocks-li-flex">
                            <div class="stocks-name">{{ item.symbol }}</div>
                            <input class="stocks-input" type="number" :placeholder="item.placeholder" v-model="form.guesses[index].guessed_price" :disabled="!canGuess">
                            <div class="stocks-control">
                                <button class="stocks-submit" @click="submitGuess(index)" :disabled="!canGuess">提交</button>
                                <button class="stocks-change" @click="submitGuess(index)" :disabled="!canGuess">更改</button>
                            </div>
                        </div>
                    </li>
                </ul>

                <button class="guess-history-btn" @click="openGuessHistoryModal">我的竞猜记录</button>
            </div>
        </div>

        <div id="footer">
            風險提示： 差價合約（CFD）屬於複雜的金融工具，由於槓桿作用，存在快速虧損資金的高風險。交易前請確保您已充分了解相關風險。
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
                <div class="history-title">我的竞猜记录</div>
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
                                    <span class="history-stock-symbol">{{ record.stock.symbol }}</span>
                                </div>

                            </div>
                            <div class="history-item-meta">
                                <span class="history-label">竞猜价格</span>
                                <div class="history-price">{{ record.guessed_price }}</div>
                            </div>
                            <div class="history-item-meta">
                                <span class="history-label">竞猜结果</span>
                                <el-tag class="history-result" :type="formatGuessStatusType(record.is_correct)" effect="light" round>
                                    {{ formatGuessStatus(record.is_correct) }}
                                </el-tag>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div v-else class="history-empty">
                <el-empty description="暂无竞猜记录" />
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
                <div class="login-title">美股差價合約價格預測</div>
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
                        <div class="mobile-login-title">美股差價合約價格預測</div>
                        <el-form-item>
                            <el-input v-model="loginForm.uid" placeholder="請輸入你的UID。"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-input v-model="loginForm.email" placeholder="請輸入你的註冊電郵地址。"></el-input>
                        </el-form-item>
                        <el-form-item class="login-item">
                            <el-button type="primary" class="login-submit" @click="handleLogin">登入</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<style>
.el-dropdown-menu__item:not(.is-disabled):hover, .el-dropdown-menu__item:not(.is-disabled):focus {
    color: #000000;
}
.login-dialog {
    border-radius: 20px !important;
    overflow: hidden;
    padding: 0 !important;
    background-color: transparent !important;
    box-shadow: none !important;
}
.login-dialog .el-dialog__header {
    display: none;
}
.login-close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    box-shadow: 0px 1px 7px 1px #ccc;
    /* background: rgba(0, 0, 0, 0.12); */
    color: #555;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    z-index: 10;
}
.login-close-btn:hover {
    background: rgba(0, 0, 0, 0.22);
    color: #111;
}
.login-dialog .el-dialog__body {
    padding: 0 !important;
}

.history-dialog {
    border-radius: 20px !important;
    overflow: hidden;
    padding: 0 !important;
    background: linear-gradient(180deg, #06242a 0%, #0d1114 100%) !important;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.45) !important;
}

.history-dialog .el-dialog__header {
    display: none;
}

.history-dialog .el-dialog__body {
    padding: 0 !important;
}

.history-box {
    position: relative;
    padding: 30px;
    color: #fff;
}

.history-close-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 50%;
    box-shadow: 0px 1px 7px 1px rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.08);
    color: #fff;
    font-size: 14px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    z-index: 10;
    border:1px solid #fff;
}

.history-close-btn:hover {
    background: rgba(255, 255, 255, 0.16);
}

.history-header {
    padding-right: 48px;
    margin-bottom: 24px;
}

.history-title {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.history-subtitle {
    margin-top: 8px;
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
}

.history-list {
    display: flex;
    flex-direction: column;
    gap: 18px;
    max-height: 60vh;
    overflow-y: auto;
    padding-right: 6px;
}

.history-group {
    border: 1px solid rgba(0, 194, 184, 0.25);
    border-radius: 18px;
    background: rgba(0, 194, 184, 0.06);
    padding: 18px;
}

.history-group-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 14px;
    color: #00ddce;
}

.history-items {
    display: grid;
    gap: 12px;
}

.history-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
    border-radius: 14px;
    padding: 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.history-item-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.history-stock {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.history-stock-symbol {
    font-size: 16px;
    font-weight: 700;
}

.history-stock-name {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.65);
}

.history-price {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
}

.history-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    color: rgba(255, 255, 255, 0.72);
}

.history-label {
    font-size: 15px;
}

.history-empty {
    padding: 16px 0 8px;
}

.guess-history-btn {
    margin-top:50px;
    border: 1px solid #00c2b8;
    background: linear-gradient(180deg, rgba(0, 194, 184, 0.18) 0%, rgba(0, 194, 184, 0.08) 100%);
    color: #fff;
    box-shadow: 0px 1px 7px 1px rgba(0, 194, 184, 0.15);
    padding: 12px 24px;
    border-radius: 30px;
    font-size: 16px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.guess-history-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0px 10px 24px rgba(0, 194, 184, 0.18);
}

.language-btn:focus-visible{
    outline: none !important;
}

@media (max-width: 1000px) {
    .el-dialog.login-dialog {
        width: 95% !important;
        border-radius: 12px !important;
    }

    .el-dialog.history-dialog {
        width: 95% !important;
        border-radius: 12px !important;
    }

    .history-box {
        padding: 20px;
    }

    .history-title {
        font-size: 22px;
    }

    .history-item-main {
        align-items: flex-start;
        flex-direction: column;
    }
}

</style>

<style scoped>
/* Remove number spinner arrows for webkit and firefox */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance: textfield;
}
.mobile-login-title{
    display: none;
}
#header{
    text-align: center;
    position: fixed;
    width: 100%;
    background-color: #fff;
    top: 0;
    z-index: 1111;
    box-shadow: 0px 3px 13px 0px #0000006e;
}

.header-flex{
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.header-box{
    width: 90%;
    padding: 18px 0;
    display: inline-block;
}
.logo img{
    height: 40px;
}
.box{
    width: 1160px;
    display: inline-block;
}
.menu{
    display: flex;
    gap: 20px;
}
.login-btn{
    box-shadow: 0px 1px 7px 1px #ccc;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 16px;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    justify-content: center;
    align-items: center;
    gap: 8px;
}
.login-btn img{
    width: 15px;
}
.language-btn{
    box-shadow: 0px 1px 7px 1px #ccc;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 16px;
    line-height:unset;
    color: #000;
}
.el-dropdown-menu__item{
    font-size: 16px;
}

#banner{
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
    margin-top: 60px;
}
.banner-box{
    text-align: left;
    width: 1200px;
}
.banner-text{
    margin-bottom: 10px;
}
.banner-spec-text p{
    background: linear-gradient(180deg, #FFFFFF 30.29%, #ED650D 100%);
    background-clip: text;
    -webkit-background-clip: text;
    font-weight: bold;
    -webkit-text-fill-color: transparent;
    line-height: normal;
}

#container{
    padding: 40px 0 0 0;
    background: linear-gradient(180deg, rgba(26, 26, 26, 0) 0%, #002127 2.41%, #252525 100%);
    text-align: center;
    color: #fff;
}

.remaining-time {
    border: 2px solid #00c2b8;
    background: linear-gradient(180deg, rgba(25, 108, 121, 0.7) 0%, #000000 100%);
    padding: 24px 0;
    text-align: center;
    border-radius: 10px;
    margin-bottom: 30px;
}
.time-title {
    font-size: 24px;
    color: #fff;
    margin-bottom: 12px;
    font-weight: lighter;
}

.cur-date{
    display: inline-block;
    border: 1px solid #00c2b8;
    color: #fff;
    padding: 15px 20px;
    border-radius: 20px;
    line-height: normal;
    margin: 30px 0;
}

.cur-date-flex{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    font-weight: lighter;
    font-size: 20px;
}

.cur-date-flex img{
    width: 18px;
    height: 18px;
}
.time-display {
    display: flex;
    justify-content: center;
    align-items: flex-start;
}
.time-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 60px;
}
.time-value {
    font-size: 55px;
    line-height: 1;
    color: #FFFFFF;
    font-family: 'Gilroy-Medium';
}
.time-label {
    font-size: 16px;
    color: #fff;
    margin-top: 8px;
}
.time-separator {
    font-size: 55px;
    line-height: 1;
    color: #FFFFFF;
    margin: 0 10px;
    position: relative;
    top: -4px;
}

.time-tips{
    margin: 40px 0;
}

.time-tips1{
    font-size: 24px;
    font-weight: bold;
}

.time-tips2{
    color:#00DDCE;
    font-weight: 500;
    margin: 10px 0px 20px 0px;
}

.time-tips3{
    font-weight: lighter;
    font-size: 15px;
}

.stocks-box{
    margin: 50px 0;
}
.stocks-ul{
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
}

.stocks-li{
    background-image: url('/images/stocks-bg.webp');
    background-repeat: no-repeat;
    background-size: 100%;
    background-position: top;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    justify-content: space-around;
    padding: 20px;
    border: 1px solid #00c2b8;
    align-items: center;
}

.stocks-li img{
    width: 170px;
}

.stocks-li-flex{
    display: flex;
    flex-direction: column;
    flex-wrap: nowrap;
    align-content: flex-start;
    justify-content: center;
    align-items: flex-start;
    gap: 20px;
}

.stocks-name{
    font-size: 24px;
    font-weight: bold;
    line-height: normal;
}

.stocks-input{
    padding: 5px 10px;
    border-radius: 10px;
    border: 1px solid #00c2b8;
    color: #000;
    background: #ECECECD1;
    width: 80%;
}

.stocks-input::placeholder{
    color: #000000c9;
}

.stocks-control{
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    justify-content: center;
    align-items: center;
    gap: 20px;
}

.stocks-submit{
    padding: 5px 20px;
    border-radius: 20px;
    border: 1px solid #00c2b8;
    color: #fff;
    background: #034854;
    cursor: pointer;
    box-shadow: 0px 0px 3.73px 0px #00DDCECC;
    transition: all 0.3s;
}

.stocks-submit:hover{
    background-color: #026F72;
}
.stocks-change{
    padding: 5px 20px;
    border-radius: 20px;
    color: #fff;
    background: #000000;
    cursor: pointer;
    box-shadow: 0px 0px 3.73px 0px #00DDCECC;
    transition: all 0.3s;
}

.stocks-change:hover{
    background-color: #313131;
}

#footer{
    color: #fff;
    text-align: center;
    padding: 20px 0;
    border-top: 1px solid #00DDCE6E;
    width: 1400px;
    margin: 0 auto;
    font-size: 14px;
}

/* Login Modal Styles */
.login-box {
    display: flex;
    width: 100%;
    min-height: 450px;
    background: #FAFAFA;
    border-radius: 20px;
    overflow: hidden;
}

.login-left {
    flex: 0 0 50%;
    background: #000;
    position: relative;
    overflow: hidden;
}

.login-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.login-right {
    flex: 1;
    padding: 40px 60px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    background: #F8F8F8;
}

.login-lang {
    position: absolute;
    top: 25px;
    right: 60px;
}

.lang-btn {
    border: 1px solid #EAEAEA;
    background: #FFF;
    padding: 10px 20px;
    border-radius: 20px;
    font-size: 14px;
    color: #333;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.02);
}

.form {
    margin-top: 20px;
    width: 100%;
}

:deep(.login-right .el-form-item) {
    margin-bottom: 25px;
}

:deep(.login-right .el-input__wrapper) {
    background-color: #F2F2F2 !important;
    box-shadow: none !important;
    border-radius: 10px;
    padding: 12px 20px;
}

:deep(.login-right .el-input__inner) {
    height: 30px;
    font-size: 15px;
    color: #333;
}
:deep(.login-right .el-input__inner::placeholder) {
    color: #999;
}

.login-submit {
    width: 100%;
    background-color: #E25A2B !important;
    border-color: #E25A2B !important;
    border-radius: 30px !important;
    height: 50px !important;
    font-size: 20px !important;
    font-weight: 500 !important;
    margin-top: 10px;
    transition: all 0.3s;
}

.login-submit:hover {
    background-color: #C74D23 !important;
    border-color: #C74D23 !important;
}

.login-title{
    position: absolute;
    color: #fff;
    top: 40px;
    left: 0;
    right: 0;
    margin: auto 0;
    text-align: center;
    font-size: 28px;
    font-weight: bold;
    background: linear-gradient(180deg, #FFFFFF 30.29%, #ED650D 100%);
    background-clip: text;
    -webkit-background-clip: text;
    font-weight: bold;
    -webkit-text-fill-color: transparent;
    line-height: normal;
}

.history-result{
    font-size: 15px;
}
@media (max-width: 1400px) {
    #footer{
        width: 95%;
    }
}
@media (max-width: 1280px) {
    .banner-box{
        width: 85%;
    }

    .box{
        width: 95%;
    }

    #banner{
        font-size: 32px;
        height: 450px;
    }

    .stocks-li img{
        width: 30%;
    }

    .time-value,.time-separator{
        font-size: 32px;
    }

    .time-title{
        font-size: 18px;
    }

}
@media (max-width: 1000px) {
    .banner-box{
        width: 90%;
    }
    .stocks-li img {
        width: 30%;
        height: fit-content;
    }
    .stocks-li {
        align-items: center;
        padding: 20px 0;
    }
    .stocks-li-flex{
        gap: 10px;
    }
    .stocks-input{
        margin-bottom: 10px;
    }

    .stocks-li{
        background-size: cover;
    }

    .stocks-name{
        font-size: 18px;
    }

}
@media (max-width: 650px) {
    .mobile-login-title{
        display: block;
        font-size: 20px;
        text-align: center;
        margin-bottom: 20px;
        font-weight: bold;
        color: #E25A2B;
    }
    #banner{
        height: 250px;
        font-size: 18px;
    }

    .time-tips1{
        font-size: 16px;
    }

    .time-tips2{
        font-size: 15px;
    }

    .stocks-ul {
        grid-template-columns: repeat(1, 1fr);
        gap: 15px;
    }
    .stocks-li {
        align-items: center;
        padding: 20px;
        background-size: cover;
    }

    .stocks-submit,.stocks-change{
        font-size: 14px;
    }

    .stocks-box{
        margin: 30px 0;
    }

    .stocks-name{
        font-size: 18px;
    }

    .cur-date-flex{
        font-size: 18px;
    }

    .login-left{
        display: none;
    }

    .login-right{
        padding: 10px 20px;
    }

    .login-box{
        min-height: 350px;
    }

    .login-submit{
        font-size: 16px !important;
    }
    .login-item{
        margin-bottom: 0 !important;
    }
    .menu{
        gap: 10px;
    }
    .login-btn,.language-btn{
        font-size: 15px;
        padding: 5px 10px;
    }

    .header-box{
        width: 95%;
    }
    .cur-date{
        padding: 8px 20px;
    }
    .logo img {
        height: 30px;
    }
    .banner-spec-text p{
        line-height: unset;
    }
    .history-title {
        font-size: 18px;
    }
    .history-price {
        font-size: 16px;

    }
    .guess-history-btn{
        margin-top: 30px;
    }
}

.stocks-submit:disabled, .stocks-change:disabled {
    background-color: #555555 !important;
    border-color: #666666 !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
    opacity: 0.6;
}
.stocks-input:disabled {
    background: #ECECECD1 !important;
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
