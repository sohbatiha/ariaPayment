<?php


namespace Sohbatiha\AriaPayment\Drivers;


use Illuminate\Http\Request;
use Sohbatiha\AriaPayment\Invoice;
use Sohbatiha\AriaPayment\Models\Transaction;
use SoapClient;

class Saman extends Driver
{
    private $amount;
    protected $token;

    public function execute()
    {
        $amount = $this->invoice->getAmount();
        $res_num = $this->getResNumber();
        $MID = config('aria_payment.saman.MID');
        $request_token_url = "https://verify.sep.ir/Payments/InitPayment.asmx?WSDL";


        $soap = new SoapClient($request_token_url);

        $response = $soap->RequestToken($MID, $res_num, $amount);

        $status = (int)$response;

        if ($status < 0) {
            $this->purchaseFailed($response);
        }


        $this->saveTransaction([
            'invoice_id' => $this->invoice->invoice->id,
            'ref_id' => null,
            'res_id' => $res_num,
            'amount' => $amount,
            'status' => self::WAITING,
            'data' => [
                'ip' => request()->ip(),
                'user_id' => auth()->user()->id ?? null,
                'driver' => $this->getDriverName()
            ],
        ]);

        $this->token = $response;

        $this->submitMethod = "POST";
        $this->submitData = [
            "Token" => $response,
            "RedirectURL" => $this->generateCallbackUrl($this->transaction->id)
        ];

        return $this;

    }

    public function verify()
    {

        $state = request()->State;
        $stateCode = request()->StateCode;
        $resNum = request()->ResNum;
        $refNum = request()->RefNum;
        $CID = request()->CID; //shomare kart kharidar be sorate code shode
        $TRACENO = request()->TRACENO; //shomare peygiri tolid shode tavasote sep
        $RRN = request()->RRN;
        $securePan = request()->SecurePan; //shomare kart kharidar
        $MID = config('aria_payment.saman.MID');

        if ($state != 'OK') {
            throw new \Exception($this->mapStateCodeToMessage($stateCode), $stateCode);
        }

        if (Transaction::where('ref_id', $refNum)->count() > 0) {
            throw new \Exception("این رسید دیجیتالی قبلا استفاده شده است ." , null);
        }

        try {
            $soapclient = new soapClient('https://verify.sep.ir/payments/referencepayment.asmx?WSDL');
            $res = $soapclient->verifyTransaction($refNum, $MID);

        } catch (\Exception $exception) {
            throw new \Exception('failed connect to bank for verify request' , null);
        }

        $transaction = $this->transaction;

        if ($res <= 0) {
            $bankStateCode = $res ;
            $bankStateMessage = $this->mapVerifyTransactionStateCodeToMessage($stateCode);

            $transaction->status = self::FAILED;
            $transaction->ref_id = $refNum;
            $transaction->save();

            throw new \Exception($bankStateMessage, $bankStateCode);
        } else if ($res != $transaction->amount) {
            $bankStateCode = null ;
            $bankStateMessage = 'مبلغ پرداخت شده با مبلغ فاکتور متفاوت می باشد .';

            $transaction->status = self::FAILED;
            $transaction->ref_id = $refNum;
            $transaction->save();

            throw new \Exception($bankStateMessage, $bankStateCode);
        }

        $transaction->status = self::SUCCESSFUL;
        $transaction->ref_id = $refNum;
        $transaction->save();


    }

    public function toJson()
    {
        // TODO: Implement toJson() method.
    }

    public function saveTransaction($transaction)
    {
        $this->transaction = Transaction::create($transaction);
    }


    public function mapVerifyTransactionStateCodeToMessage($code): string
    {
        return [
            -1 => 'خطا در پردازش اطلاعات ارسالی (مشکل در یکی از ورودی ها و ناموفق بودن فراخوانی متد برگشت تراکنش)',
            -3 => 'ورودیها حاوی کارکترهای غیرمجاز میباشند.',
            -4 => 'کلمه عبور یا کد فروشنده اشتباه است (Merchant Authentication Failed)',
            -6 => 'سند قبال برگشت کامل یافته است. یا خارج از زمان 30 دقیقه ارسال شده است.',
            -7 => 'رسید دیجیتالی تهی است.',
            -8 => 'طول ورودیها بیشتر از حد مجاز است.',
            -9 => 'وجود کارکترهای غیرمجاز در مبلغ برگشتی.',
            -10 => 'رسید دیجیتالی به صورت Base64 نیست (حاوی کاراکترهای غیرمجاز است)',
            -11 => 'طول ورودیها ک تر از حد مجاز است.',
            -12 => 'مبلغ برگشتی منفی است.',
            -13 => 'مبلغ برگشتی برای برگشت جزئی بیش از مبلغ برگشت نخوردهی رسید دیجیتالی است.',
            -14 => 'چنین تراکنشی تعریف نشده است.',
            -15 => 'مبلغ برگشتی به صورت اعشاری داده شده است.',
            -16 => 'خطای داخلی سیستم',
            -17 => 'برگشت زدن جزیی تراکنش مجاز نمی باشد.',
            -18 => 'IP Address فروشنده نا معتبر است و یا رمز تابع بازگشتی (reverseTransaction) اشتباه است.',
            -111 => 'مرچنت آي دی نمی تواند نال باشد.'
        ][$code];
    }

    public function mapStateCodeToMessage($code): string
    {
        return [
            0 => 'تراکنش با موفقیت پذیرفته شده است .',
            -1 => 'کاربر از انجام تراکنش صرف نظر کرد .',
            3 => 'پذیرنده فروشگاهی نامعتبر است .',
            5 => 'از انجام تراکنش صرف نظر شد .',
            8 => 'با تشخیص هویت دارنده کارت تراکنش موفق می باشد .',
            12 => 'تراکنش نامعتبر است .',
            14 => 'شماره کارت ارسالی نامعتبر است .',
            15 => 'چنین صادر کننده کارتی وجود ندارد .',
            16 => 'تراکنش مورد تأیید است و اطالعات شیار سوم کارت به روز رسانی شود .',
            19 => 'تراکنش مجدداً ارسال شود .',
            23 => 'کارمزد ارسالی پذیرنده غیر قابل قبول است .',
            30 => 'قالب پیام دارای اشکال است .',
            31 => 'پذیرنده توسط سوییچ پشتیبانی ن ی شود .',
            33 => 'از تاریخ انقضای کارت گذشته است و کارت دیگر معتبر نیست .',
            34 => 'خریدار یا فیلد CVV2 و یا فیلد ExpDate را اشتباه وارد کرده است )یا دارنده کارت مظنون به تقلب است( .',
            38 => 'تعداد دفعات ورود رمز غلط بیش از حد مجاز است .',
            39 => 'کارت حساب اعتباری ندارد .',
            40 => 'ع لیات درخواستی پشتیبانی ن ی گردد .',
            41 => 'کارت مفقودی می باشد .',
            42 => 'کارت حساب ع ومی ندارد .',
            43 => 'کارت مسروقه می باشد .',
            44 => 'کارت حساب سرمایه گذاری ندارد .',
            51 => 'موجودی کافی نیست .',
            52 => 'کارت حساب جاری ندارد .',
            53 => 'کارت حساب قرض الحسنه ندارد .',
            54 => 'تاریخ انقضای کارت سپری شده است .',
            55 => 'خریدار رمز کارت )PIN )را اشتباه وارد کرده است .',
            56 => 'کارت نامعتبر است .',
            57 => 'انجام تراکنش مربوطه توسط دارنده کارت مجاز ن ی باشد .',
            58 => 'انجام تراکنش مربوطه توسط پایانه انجام دهنده مجاز ن ی باشد .',
            61 => 'مبلغ تراکنش بیش از حد مجاز است .',
            62 => 'کارت محدود شده است .',
            63 => 'ت هیدات امنیتی نقض گردیده است .',
            65 => 'تعداد درخواست تراکنش بیش از حد مجاز است .',
            68 => 'تراکنش در شبکه بانکی _ Timeout خورده است .',
            75 => 'تعداد دفعات ورود رمز غلط بیش از حد مجاز است .',
            79 => 'مبلغ سند برگشتی، از مبلغ تراکنش اصلی بیشتر است .',
            84 => 'سیستم بانک صادر کننده کارت خریدار، در وضعیت عملیاتی نیست .',
            90 => 'سامانه مقصد تراکنش در حال انجام ع لیات پایان روز می باشد .',
            93 => 'تراکنش Authorize شده است )شماره PIN و PAN درست هستند( ولی امکان سند خوردن وجود ندارد .',
            96 => 'کلیه خطاهای دیگر بانکی باعث ایجاد چنین خطایی گردید .',

        ][$code];
    }


    public function getDriverName(): string
    {
        return 'saman';
    }

}
