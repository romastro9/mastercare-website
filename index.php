<?php
session_start();
const DATA_FILE = __DIR__ . '/data/site.json';
function load_site(){
    if(!file_exists(DATA_FILE)){ return ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]]; }
    $data = json_decode(file_get_contents(DATA_FILE), true);
    return is_array($data) ? $data : ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]];
}
function save_site($data){
    if(!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data', 0775, true);
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function active_items($items){ return array_values(array_filter($items ?? [], fn($x)=> !isset($x['active']) || $x['active'])); }
$data = load_site();
$s = $data['settings'] ?? [];
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
        $flash = 'Thank you. MasterCare will contact you soon.';
    } else {
        $flash = 'Please enter your name and phone number.';
    }
}
$title = $s['seo_title'] ?? ($s['clinic_name'] ?? 'MasterCare Premium');
$desc = $s['seo_description'] ?? 'Premium beauty and aesthetic clinic.';
$services = active_items($data['services'] ?? []);
$promotions = active_items($data['promotions'] ?? []);
$products = active_items($data['products'] ?? []);
$gallery = active_items($data['gallery'] ?? []);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:wght@300;400;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="nav">
  <a class="brand" href="#home"><span>MC</span><b><?= e($s['clinic_name'] ?? 'MasterCare Premium') ?></b></a>
  <nav><a href="#services">Services</a><a href="#promotions">Promotions</a><a href="#results">Results</a><a href="#booking">Booking</a><a href="admin.php">Admin</a></nav>
</header>
<section id="home" class="hero">
  <div class="hero-copy">
    <p class="eyebrow"><?= e($s['slogan'] ?? 'Your beauty is our profession') ?></p>
    <h1><?= e($s['hero_title'] ?? 'Premium Skin Care & Aesthetic Clinic') ?></h1>
    <p><?= e($s['hero_subtitle'] ?? '') ?></p>
    <div class="hero-actions"><a class="btn" href="#booking">Book Consultation</a><a class="btn ghost" href="#services">View Services</a></div>
  </div>
  <div class="hero-card"><div class="logo-mark">MC</div><h2><?= e($s['clinic_name_kh'] ?? 'MasterCare Premium') ?></h2><p>Premium beauty • natural result • clean clinic care</p></div>
</section>
<?php if($flash): ?><div class="notice"><?= e($flash) ?></div><?php endif; ?>
<section class="section intro">
  <div><p class="eyebrow">About MasterCare</p><h2>Safe aesthetic care with premium customer experience.</h2></div>
  <p>MasterCare focuses on skin booster, facial lifting, filler, Botox, body slimming, laser, and personalized beauty consultation for natural-looking results.</p>
</section>
<section id="services" class="section">
  <p class="eyebrow center">Our Services</p><h2 class="center">Aesthetic & Skin Care Treatments</h2>
  <div class="grid cards">
  <?php foreach($services as $item): ?>
    <article class="card"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?><span><?= e($item['category'] ?? '') ?></span><h3><?= e($item['name'] ?? '') ?></h3><p><?= e($item['description'] ?? '') ?></p><b><?= e($item['price'] ?? 'Contact') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>
<section id="promotions" class="section soft">
  <p class="eyebrow center">Promotions</p><h2 class="center">Current Beauty Offers</h2>
  <div class="grid cards">
  <?php foreach($promotions as $item): ?>
    <article class="card promo"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?><h3><?= e($item['title'] ?? '') ?></h3><p><?= e($item['description'] ?? '') ?></p><b><?= e($item['price'] ?? '') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>
<section id="results" class="section">
  <p class="eyebrow center">Before & After</p><h2 class="center">Result Gallery</h2>
  <div class="grid results">
  <?php foreach($gallery as $item): ?>
    <article class="result"><div class="ba"><div><?php if(!empty($item['before'])):?><img src="<?= e($item['before']) ?>" alt="Before"><?php else:?><span>Before</span><?php endif;?></div><div><?php if(!empty($item['after'])):?><img src="<?= e($item['after']) ?>" alt="After"><?php else:?><span>After</span><?php endif;?></div></div><h3><?= e($item['title'] ?? '') ?></h3><p><?= e($item['note'] ?? 'Results may vary by individual condition.') ?></p></article>
  <?php endforeach; ?>
  </div>
</section>
<section class="section soft">
  <p class="eyebrow center">Products</p><h2 class="center">Premium Treatment Products</h2>
  <div class="grid cards">
  <?php foreach($products as $item): ?>
    <article class="card"><?php if(!empty($item['image'])): ?><img src="<?= e($item['image']) ?>" alt=""><?php endif; ?><span><?= e($item['category'] ?? '') ?></span><h3><?= e($item['name'] ?? '') ?></h3><p><?= e($item['description'] ?? '') ?></p><b><?= e($item['price'] ?? '') ?></b></article>
  <?php endforeach; ?>
  </div>
</section>
<section id="booking" class="section booking">
  <div><p class="eyebrow">Book Consultation</p><h2>Contact MasterCare</h2><p><b>Phone:</b> <?= e($s['phone'] ?? '') ?></p><p><b>Address:</b> <?= e($s['address'] ?? '') ?></p><p><b>Opening:</b> <?= e($s['hours'] ?? '') ?></p><div class="hero-actions"><a class="btn ghost" href="<?= e($s['facebook'] ?? '#') ?>" target="_blank">Facebook</a><a class="btn ghost" href="<?= e($s['messenger'] ?? '#') ?>" target="_blank">Messenger</a></div></div>
  <form method="post" class="form"><input type="hidden" name="action" value="booking"><input name="name" placeholder="Customer name" required><input name="phone" placeholder="Phone number" required><select name="service"><option value="">Select service</option><?php foreach($services as $item): ?><option><?= e($item['name'] ?? '') ?></option><?php endforeach; ?></select><input type="date" name="date"><textarea name="message" placeholder="Message"></textarea><button class="btn" type="submit">Send Booking</button></form>
</section>
<footer><b><?= e($s['clinic_name'] ?? 'MasterCare Premium') ?></b><p><?= e($s['slogan'] ?? '') ?></p></footer>
</body>
</html>
