<script setup lang="ts">
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { ElMessage } from 'element-plus'

const props = defineProps<{
    stocks?: Array<{ id: number; symbol: string; name: string }>;
    canGuess?: boolean;
    existingGuesses?: Record<number, { guessed_price: number }>;
}>();

const page = usePage();
const user = computed(() => page.props.auth?.user as any);

const { locale } = useI18n();
const changeLanguage = (command: string) => {
    locale.value = command;
};

const loginModalVisible = ref(false);

const openloginModal = () => {
    loginModalVisible.value = true;
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

const updateCountdown = () => {
    const now = new Date();
    const deadlineUTC = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate(), 15, 0, 0);
    let diff = deadlineUTC - now.getTime();
    if (diff < 0) {
        const tomorrowUTC = Date.UTC(now.getUTCFullYear(), now.getUTCMonth(), now.getUTCDate() + 1, 15, 0, 0);
        diff = tomorrowUTC - now.getTime();
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

onMounted(() => {
    updateCountdown();
    timer = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="US Stock CFD Price Guess - Vantage" />

    <section id="header">
        <div class="header-box">
            <div class="header-flex">
                <div class="logo">
                    <img src="/images/logo.png" alt="Vantage Hero Banner" />
                </div>

                <div class="menu">
                    <template v-if="!user">
                        <button class="login-btn" @click="openloginModal"><img src="/images/login-ico.jpg"/>{{ $t('login') }}</button>
                    </template>
                    <template v-else>
                        <el-dropdown @command="handleLogout">
                            <div class="language-btn" style="cursor: pointer;">
                                {{ user.email }}
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
                <div class="cur-date-flex"><img src="/images/date.svg"/>2026年7月8日</div>
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
                <div class="time-tips1">請在18:00(GMT+3)前提交估算的收市價。</div>
                <div class="time-tips2">輸入產品收盤價的整數部分（例：若產品實際價格為 396.72，則預測 396 將被視為正確預測，而預測 397 則被視為錯誤預測）。</div>
                <div class="time-tips3">*本日收市價以MT5VantageMarkets-Live系統時間23:59為準。</div>
            </div>

            <div class="stocks-box">
                <ul class="stocks-ul">
                    <li class="stocks-li" v-for="(item, index) in stocksList" :key="item.id">
                        <img :src="item.images" alt="">
                        <div class="stocks-li-flex">
                            <div class="stocks-name">{{ item.name }}</div>
                            <input class="stocks-input" type="number" :placeholder="item.placeholder" v-model="form.guesses[index].guessed_price">
                            <div class="stocks-control">
                                <button class="stocks-submit" @click="submitGuess(index)">提交</button>
                                <button class="stocks-change" @click="submitGuess(index)">更改</button>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <div id="footer">
            風險提示： 差價合約（CFD）屬於複雜的金融工具，由於槓桿作用，存在快速虧損資金的高風險。交易前請確保您已充分了解相關風險。
        </div>
    </section>


    <el-dialog
      v-model="loginModalVisible"
      width="850px"
      class="login-dialog"
      :show-close="false"
      destroy-on-close
      append-to-body
    >
        <div class="login-box">
            <div class="login-left">
                <!-- <div class="login-title">美股差價合約價格預測</div> -->
                <img src="/images/login-img.webp" class="login-img"/>
            </div>
            <div class="login-right">
                <div class="login-lang">
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
                </div>
                <div class="form">
                    <el-form :model="loginForm">
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
.login-dialog .el-dialog__body {
    padding: 0 !important;
}

@media (max-width: 650px) {
    .el-dialog.login-dialog {
        width: 95% !important;
        border-radius: 12px !important;
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
    padding: 15px 0;
    display: inline-block;
}
.logo img{
    height: 26.5px;
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
    background-image: url('images/banner.webp');
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
    font-size: 32px;
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
    font-size: 16px;
    color: #E0E0E0;
    margin-bottom: 12px;
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
    font-size: 42px;
    line-height: 1;
    color: #FFFFFF;
    font-family: 'Gilroy';
}
.time-label {
    font-size: 14px;
    color: #fff;
    margin-top: 8px;
}
.time-separator {
    font-size: 42px;
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
    background-image: url('images/stocks-bg.webp');
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
    right: 30px;
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

@media (max-width: 1150px) {
    .banner-box{
        width: 85%;
    }
    #footer{
        width: 95%;
    }
    .box{
        width: 95%;
    }

    #banner{
        height: 450px;
    }

    .stocks-li img{
        width: 40%;
    }

    .time-value{
        font-size: 32px;
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
}
@media (max-width: 650px) {
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
        padding: 20px 0;
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
    .form {
        margin-top: 60px;
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
}
</style>
