<?php
session_start();
require_once __DIR__ . '/config.php';

function mc_default_data(){
    return ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]];
}
function load_site(){
    if(!file_exists(MC_DATA_FILE)) return mc_default_data();
    $data = json_decode(file_get_contents(MC_DATA_FILE), true);
    return is_array($data) ? array_merge(mc_default_data(), $data) : mc_default_data();
}
function save_site($data){
    if(!is_dir(dirname(MC_DATA_FILE))) mkdir(dirname(MC_DATA_FILE), 0775, true);
    file_put_contents(MC_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_items($items){ return array_values(array_filter($items ?? [], fn($x)=> !isset($x['active']) || $x['active'])); }
function lang_value($arr, $key, $lang, $fallback=''){
    $kh = $key . '_kh';
    if($lang === 'kh' && !empty($arr[$kh])) return $arr[$kh];
    return $arr[$key] ?? $fallback;
}

$data = load_site();
$s = $data['settings'] ?? [];
$lang = ($_GET['lang'] ?? $_SESSION['mc_lang'] ?? 'en') === 'kh' ? 'kh' : 'en';
$_SESSION['mc_lang'] = $lang;
$flash = '';

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'booking'){
    $booking = [
        'id' => 'bk'.time().rand(100,999),
        'name' => trim($_POST['name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'service' => trim($_POST['service'] ?? ''),
        'date' => trim($_POST['date'] ?? ''),
        'message' => trim($_POST['message'] ?? ''),
        'status' => 'New',
        'created_at' => date('Y-m-d H:i')
    ];
    if($booking['name'] && $booking['phone']){
        $data['bookings'][] = $booking;
        save_site($data);
        $flash = $lang === 'kh' ? 'សូមអរគុណ។ MasterCare នឹងទាក់ទងទៅអ្នកឆាប់ៗ។' : 'Thank you. MasterCare will contact you soon.';
    } else {
        $flash = $lang === 'kh' ? 'សូមបញ្ចូលឈ្មោះ និងលេខទូរស័ព្ទ។' : 'Please enter your name and phone number.';
    }
}

$services = active_items($data['services'] ?? []);
$promotions = active_items($data['promotions'] ?? []);
$products = active_items($data['products'] ?? []);
$gallery = active_items($data['gallery'] ?? []);
$title = lang_value($s, 'seo_title', $lang, lang_value($s, 'clinic_name', $lang, 'MasterCare Premium'));
$desc = lang_value($s, 'seo_description', $lang, 'Premium beauty and aesthetic clinic in Cambodia.');
$labels = [
    'services' => $lang==='kh' ? 'សេវាកម្ម' : 'Services',
    'promotions' => $lang==='kh' ? 'ប្រូម៉ូសិន' : 'Promotions',
    'results' => $lang==='kh' ? 'លទ្ធផល' : 'Results',
    'booking' => $lang==='kh' ? 'កក់ពេល' : 'Booking',
    'admin' => $lang==='kh' ? 'Admin' : 'Admin',
    'book_now' => $lang==='kh' ? 'កក់ពេលពិគ្រោះ' : 'Book Consultation',
    'view_services' => $lang==='kh' ? 'មើលសេវាកម្ម' : 'View Services',
    'about' => $lang==='kh' ? 'អំពី MasterCare' : 'About MasterCare',
    'about_title' => $lang==='kh' ? 'ការថែរក្សាសម្រស់ប្រកបដោយសុវត្ថិភាព និងបទពិសោធន៍ពិសេស។' : 'Safe aesthetic care with premium customer experience.',
    'about_desc' => $lang==='kh' ? 'MasterCare ផ្តោតលើ Skin Booster, Face Lifting, Filler, Botox, Slimming, Laser និងការពិគ្រោះសម្រស់ផ្ទាល់ខ្លួន ដើម្បីលទ្ធផលមើលទៅធម្មជាតិ។' : 'MasterCare focuses on skin booster, facial lifting, filler, Botox, body slimming, laser, and personalized beauty consultation for natural-looking results.',
    'service_title' => $lang==='kh' ? 'ការព្យាបាលស្បែក និងសម្រស់' : 'Aesthetic & Skin Care Treatments',
    'promo_title' => $lang==='kh' ? 'ការផ្តល់ជូនពិសេសប្រចាំខែ' : 'Current Beauty Offers',
    'result_title' => $lang==='kh' ? 'រូបភាពមុន និងក្រោយ' : 'Before & After Gallery',
    'product_title' => $lang==='kh' ? 'ផលិតផលព្យាបាលពិសេស' : 'Premium Treatment Products',
    'contact_title' => $lang==='kh' ? 'ទាក់ទង MasterCare' : 'Contact MasterCare',
    'send_booking' => $lang==='kh' ? 'ផ្ញើការកក់' : 'Send Booking'
];
?>
<!doctype html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css?v=2">
</head>
<body>
<header class="nav">
  <a class="brand" href="#home">
    <?php if(!empty($s['logo'])): ?><img class="brand-logo" src="<?= e($s['logo']) ?>" alt="MasterCare"><?php else: ?><span>MC</span><?php endif; ?>
    <b><?= e(lang_value($s, 'clinic_name', $lang, 'MasterCare Premium')) ?></b>
  </a>
  <button class="menu-btn" onclick="document.body.classList.toggle('menu-open')">☰</button>
  <nav>
    <a href="#services"><?= e($labels['services']) ?></a>
    <a href="#promotions"><?= e($labels['promotions']) ?></a>
    <a href="#results"><?= e($labels['results']) ?></a>
    <a href="#booking"><?= e($labels['booking']) ?></a>
    <a href="admin.php"><?= e($labels['admin']) ?></a>
    <a class="lang" href="?lang=<?= $lang==='kh'?'en':'kh' ?>"><?= $lang==='kh'?'EN':'ខ្មែរ' ?></a>
  </nav>
</header>

<section id="home" class="hero" style="<?= !empty($s['hero_image']) ? 'background-image:linear-gradient(90deg,rgba(45,33,25,.65),rgba(255,248,236,.2)),url('.e($s['hero_image']).')' : '' ?>">
  <div class="hero-copy">
    <p class="eyebrow"><?= e(lang_value($s, 'slogan', $lang, 'Your beauty is our profession')) ?></p>
    <h1><?= e(lang_value($s, 'hero_title', $lang, 'Premium Skin Care & Aesthetic Clinic')) ?></h1>
    <p><?= e(lang_value($s, 'hero_subtitle', $lang, 'Advanced beauty treatments with a premium clinic experience.')) ?></p>
    <div class="hero-actions"><a class="btn" href="#booking"><?= e($labels['book_now']) ?></a><a class="btn ghost" href="#services"><?= e($labels['view_services']) ?></a></div>
  </div>
  <div class="hero-card">
    <?php if(!empty($s['logo'])): ?><img class="hero-logo" src="<?= e($s['logo']) ?>" alt="MasterCare logo"><?php else: ?><div class="logo-mark">MC</div><?php endif; ?>
    <h2><?= e(lang_value($s, 'clinic_name', $lang, 'MasterCare Premium')) ?></h2>
    <p>Premium beauty • natural result • clean clinic care</p>
  </div>
</section>

<?php if($flash): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>

<section class="section intro">
  <div><p class="eyebrow"><?= e($labels['about']) ?></p><h2><?= e($labels['about_title']) ?></h2></div>
  <p><?= e($labels['about_desc']) ?></p>
</section>

<section id="services" class="section">
  <p class="eyebrow center"><?= e($labels['services']) ?></p><h2 class="center"><?= e($labels['service_title']) ?></h2>
  <div class="grid cards">
  <?php foreach($services as $item): ?>
    <article class="card"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt="<?= e(lang_value($item,'name',$lang,'')) ?>"><?php endif; ?><span><?= e(lang_value($item,'category',$lang,'')) ?></span><h3><?= e(lang_value($item,'name',$lang,'')) ?></h3><p><?= e(lang_value($item,'description',$lang,'')) ?></p><b><?= e($item['price'] ?? 'Contact') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>

<section id="promotions" class="section soft">
  <p class="eyebrow center"><?= e($labels['promotions']) ?></p><h2 class="center"><?= e($labels['promo_title']) ?></h2>
  <div class="grid cards">
  <?php foreach($promotions as $item): ?>
    <article class="card promo"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt="<?= e(lang_value($item,'title',$lang,'')) ?>"><?php endif; ?><h3><?= e(lang_value($item,'title',$lang,'')) ?></h3><p><?= e(lang_value($item,'description',$lang,'')) ?></p><b><?= e($item['price'] ?? '') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>

<section id="results" class="section">
  <p class="eyebrow center"><?= e($labels['results']) ?></p><h2 class="center"><?= e($labels['result_title']) ?></h2>
  <div class="grid results">
  <?php foreach($gallery as $item): ?>
    <article class="result"><div class="ba"><div><?php if(!empty($item['before'])):?><img src="<?= e($item['before']) ?>" alt="Before"><?php else:?><span>Before</span><?php endif;?></div><div><?php if(!empty($item['after'])):?><img src="<?= e($item['after']) ?>" alt="After"><?php else:?><span>After</span><?php endif;?></div></div><h3><?= e(lang_value($item,'title',$lang,'')) ?></h3><p><?= e(lang_value($item,'note',$lang,'Results may vary by individual condition.')) ?></p></article>
  <?php endforeach; ?>
  </div>
</section>

<section class="section soft">
  <p class="eyebrow center">Products</p><h2 class="center"><?= e($labels['product_title']) ?></h2>
  <div class="grid cards">
  <?php foreach($products as $item): ?>
    <article class="card"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt="<?= e(lang_value($item,'name',$lang,'')) ?>"><?php endif; ?><span><?= e(lang_value($item,'category',$lang,'')) ?></span><h3><?= e(lang_value($item,'name',$lang,'')) ?></h3><p><?= e(lang_value($item,'description',$lang,'')) ?></p><b><?= e($item['price'] ?? '') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>

<section id="booking" class="section booking">
  <div><p class="eyebrow"><?= e($labels['booking']) ?></p><h2><?= e($labels['contact_title']) ?></h2><p><b>Phone:</b> <?= e($s['phone'] ?? '') ?></p><p><b>Address:</b> <?= e(lang_value($s,'address',$lang,'')) ?></p><p><b>Opening:</b> <?= e(lang_value($s,'hours',$lang,'')) ?></p><div class="hero-actions"><a class="btn ghost" href="<?= e($s['facebook'] ?? '#') ?>" target="_blank">Facebook</a><a class="btn ghost" href="<?= e($s['messenger'] ?? '#') ?>" target="_blank">Messenger</a></div></div>
  <form method="post" class="form"><input type="hidden" name="action" value="booking"><input name="name" placeholder="<?= $lang==='kh'?'ឈ្មោះអតិថិជន':'Customer name' ?>" required><input name="phone" placeholder="<?= $lang==='kh'?'លេខទូរស័ព្ទ':'Phone number' ?>" required><select name="service"><option value=""><?= $lang==='kh'?'ជ្រើសរើសសេវាកម្ម':'Select service' ?></option><?php foreach($services as $item): ?><option><?= e(lang_value($item,'name',$lang,'')) ?></option><?php endforeach; ?></select><input type="date" name="date"><textarea name="message" placeholder="<?= $lang==='kh'?'សារ':'Message' ?>"></textarea><button class="btn" type="submit"><?= e($labels['send_booking']) ?></button></form>
</section>

<?php if(!empty($s['phone'])): ?><a class="float-btn call" href="tel:<?= e(preg_replace('/\s+/', '', explode('/', $s['phone'])[0])) ?>">☎</a><?php endif; ?>
<?php if(!empty($s['messenger'])): ?><a class="float-btn msg" href="<?= e($s['messenger']) ?>" target="_blank">💬</a><?php endif; ?>

<footer><b><?= e(lang_value($s, 'clinic_name', $lang, 'MasterCare Premium')) ?></b><p><?= e(lang_value($s, 'slogan', $lang, '')) ?></p></footer>
<script src="assets/app.js?v=2"></script>
</body>
</html>
