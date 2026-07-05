<?php
session_start();
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'MasterCare@2026'; // Change this before using the website live.
const DATA_FILE = __DIR__ . '/data/site.json';
function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function load_site(){
    if(!file_exists(DATA_FILE)){ return ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]]; }
    $data = json_decode(file_get_contents(DATA_FILE), true);
    return is_array($data) ? $data : ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]];
}
function save_site($data){
    if(!is_dir(__DIR__.'/data')) mkdir(__DIR__.'/data', 0775, true);
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function new_id($prefix){ return $prefix . time() . rand(100,999); }
function find_index($items,$id){ foreach($items as $i=>$x){ if(($x['id']??'')===$id) return $i; } return -1; }
$data = load_site();
$flash = '';
if(isset($_GET['logout'])){ session_destroy(); header('Location: admin.php'); exit; }
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'login'){
    if(($_POST['username'] ?? '') === ADMIN_USER && ($_POST['password'] ?? '') === ADMIN_PASS){ $_SESSION['mc_admin']=true; header('Location: admin.php'); exit; }
    $flash = 'Wrong username or password.';
}
if(empty($_SESSION['mc_admin'])):
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MasterCare Admin</title><link rel="stylesheet" href="assets/style.css"></head><body class="admin-login"><form class="login-card" method="post"><div class="logo-mark">MC</div><h1>Admin Login</h1><?php if($flash):?><div class="notice"><?= e($flash) ?></div><?php endif;?><input type="hidden" name="action" value="login"><input name="username" placeholder="Username" required><input name="password" type="password" placeholder="Password" required><button class="btn">Login</button><a href="index.php">Back to website</a></form></body></html>
<?php exit; endif;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action = $_POST['action'] ?? '';
    if($action === 'save_settings'){
        foreach(['clinic_name','clinic_name_kh','slogan','hero_title','hero_subtitle','phone','address','hours','facebook','messenger','telegram','map_url','seo_title','seo_description'] as $key){ $data['settings'][$key] = trim($_POST[$key] ?? ''); }
        save_site($data); $flash='Website information saved.';
    }
    if(in_array($action, ['save_service','save_promotion','save_product','save_gallery'], true)){
        $map = ['save_service'=>['services','svc'], 'save_promotion'=>['promotions','pro'], 'save_product'=>['products','prd'], 'save_gallery'=>['gallery','gal']];
        [$section,$prefix] = $map[$action]; $id = trim($_POST['id'] ?? '') ?: new_id($prefix); $idx = find_index($data[$section] ?? [], $id);
        if($section === 'services') $item = ['id'=>$id,'name'=>trim($_POST['name']??''),'category'=>trim($_POST['category']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'image'=>trim($_POST['image']??''),'active'=>!empty($_POST['active'])];
        elseif($section === 'promotions') $item = ['id'=>$id,'title'=>trim($_POST['title']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'image'=>trim($_POST['image']??''),'active'=>!empty($_POST['active'])];
        elseif($section === 'products') $item = ['id'=>$id,'name'=>trim($_POST['name']??''),'category'=>trim($_POST['category']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'image'=>trim($_POST['image']??''),'active'=>!empty($_POST['active'])];
        else $item = ['id'=>$id,'title'=>trim($_POST['title']??''),'service'=>trim($_POST['service']??''),'before'=>trim($_POST['before']??''),'after'=>trim($_POST['after']??''),'note'=>trim($_POST['note']??''),'active'=>!empty($_POST['active'])];
        if($idx >= 0) $data[$section][$idx] = $item; else $data[$section][] = $item;
        save_site($data); $flash = ucfirst($section).' saved.';
    }
    if($action === 'delete_item'){
        $section = $_POST['section'] ?? ''; $id = $_POST['id'] ?? '';
        if(isset($data[$section]) && is_array($data[$section])){ $data[$section] = array_values(array_filter($data[$section], fn($x)=>($x['id']??'') !== $id)); save_site($data); $flash='Item deleted.'; }
    }
    if($action === 'booking_status'){
        $id = $_POST['id'] ?? ''; $status = trim($_POST['status'] ?? 'New'); $idx = find_index($data['bookings'] ?? [], $id);
        if($idx >= 0){ $data['bookings'][$idx]['status'] = $status; save_site($data); $flash='Booking updated.'; }
    }
}
$s = $data['settings'] ?? [];
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MasterCare Admin Panel</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="admin-body"><aside><div class="logo-mark">MC</div><h2>MasterCare Admin</h2><a href="#dashboard">Dashboard</a><a href="#settings">Website Info</a><a href="#services">Services</a><a href="#promotions">Promotions</a><a href="#gallery">Before/After</a><a href="#products">Products</a><a href="#bookings">Bookings</a><a href="index.php" target="_blank">View Website</a><a href="admin.php?logout=1">Logout</a></aside>
<main class="admin-main"><section id="dashboard" class="admin-panel"><h1>Dashboard</h1><?php if($flash):?><div class="notice"><?= e($flash) ?></div><?php endif;?><div class="grid stats"><div><b><?= count($data['services']??[]) ?></b><span>Services</span></div><div><b><?= count($data['promotions']??[]) ?></b><span>Promotions</span></div><div><b><?= count($data['gallery']??[]) ?></b><span>Results</span></div><div><b><?= count($data['bookings']??[]) ?></b><span>Bookings</span></div></div></section>
<section id="settings" class="admin-panel"><h2>Website Information</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="save_settings"><?php foreach(['clinic_name','clinic_name_kh','slogan','hero_title','hero_subtitle','phone','address','hours','facebook','messenger','telegram','map_url','seo_title','seo_description'] as $key):?><label><?= e(str_replace('_',' ',ucwords($key,'_'))) ?><input name="<?= e($key) ?>" value="<?= e($s[$key] ?? '') ?>"></label><?php endforeach;?><button class="btn">Save Website Info</button></form></section>
<?php function text_input($name,$value=''){ echo '<label>'.e(ucwords(str_replace('_',' ',$name))).'<input name="'.e($name).'" value="'.e($value).'"></label>'; } ?>
<section id="services" class="admin-panel"><h2>Services</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="save_service"><input type="hidden" name="id" value=""><?php text_input('name'); text_input('category'); text_input('price'); text_input('image'); ?><label>Description<textarea name="description"></textarea></label><label class="check"><input type="checkbox" name="active" checked> Active</label><button class="btn">Add Service</button></form><?php render_table($data,'services',['name','category','price']); ?></section>
<section id="promotions" class="admin-panel"><h2>Promotions</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="save_promotion"><input type="hidden" name="id" value=""><?php text_input('title'); text_input('price'); text_input('image'); ?><label>Description<textarea name="description"></textarea></label><label class="check"><input type="checkbox" name="active" checked> Active</label><button class="btn">Add Promotion</button></form><?php render_table($data,'promotions',['title','price']); ?></section>
<section id="gallery" class="admin-panel"><h2>Before / After Gallery</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="save_gallery"><input type="hidden" name="id" value=""><?php text_input('title'); text_input('service'); text_input('before'); text_input('after'); ?><label>Note<textarea name="note"></textarea></label><label class="check"><input type="checkbox" name="active" checked> Active</label><button class="btn">Add Gallery Item</button></form><?php render_table($data,'gallery',['title','service','note']); ?></section>
<section id="products" class="admin-panel"><h2>Products</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="save_product"><input type="hidden" name="id" value=""><?php text_input('name'); text_input('category'); text_input('price'); text_input('image'); ?><label>Description<textarea name="description"></textarea></label><label class="check"><input type="checkbox" name="active" checked> Active</label><button class="btn">Add Product</button></form><?php render_table($data,'products',['name','category','price']); ?></section>
<section id="bookings" class="admin-panel"><h2>Booking Requests</h2><div class="table-wrap"><table><tr><th>Date</th><th>Name</th><th>Phone</th><th>Service</th><th>Status</th><th>Action</th></tr><?php foreach(($data['bookings']??[]) as $b):?><tr><td><?= e($b['created_at']??'') ?></td><td><?= e($b['name']??'') ?></td><td><?= e($b['phone']??'') ?></td><td><?= e($b['service']??'') ?></td><td><?= e($b['status']??'') ?></td><td><form method="post"><input type="hidden" name="action" value="booking_status"><input type="hidden" name="id" value="<?= e($b['id']??'') ?>"><select name="status"><option>New</option><option>Contacted</option><option>Completed</option><option>Cancelled</option></select><button class="small-btn">Update</button></form></td></tr><?php endforeach;?></table></div></section>
</main></body></html>
<?php
function render_table($data,$section,$cols){ echo '<div class="table-wrap"><table><tr>'; foreach($cols as $c) echo '<th>'.e($c).'</th>'; echo '<th>Status</th><th>Action</th></tr>'; foreach(($data[$section]??[]) as $row){ echo '<tr>'; foreach($cols as $c) echo '<td>'.e($row[$c]??'').'</td>'; echo '<td>'.(!isset($row['active'])||$row['active']?'Active':'Hidden').'</td><td><form method="post" onsubmit="return confirm(\'Delete item?\')"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="section" value="'.e($section).'"><input type="hidden" name="id" value="'.e($row['id']??'').'"><button class="small-btn danger">Delete</button></form></td></tr>'; } echo '</table></div>'; }
?>
