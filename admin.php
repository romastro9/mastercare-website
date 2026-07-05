<?php
session_start();
require_once __DIR__ . '/config.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function default_data(){ return ['settings'=>[], 'services'=>[], 'promotions'=>[], 'products'=>[], 'gallery'=>[], 'bookings'=>[]]; }
function load_site(){
    if(!file_exists(MC_DATA_FILE)) return default_data();
    $data = json_decode(file_get_contents(MC_DATA_FILE), true);
    return is_array($data) ? array_merge(default_data(), $data) : default_data();
}
function save_site($data){
    if(!is_dir(dirname(MC_DATA_FILE))) mkdir(dirname(MC_DATA_FILE), 0775, true);
    file_put_contents(MC_DATA_FILE, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES), LOCK_EX);
}
function new_id($prefix){ return $prefix . time() . rand(100,999); }
function find_index($items,$id){ foreach($items as $i=>$x){ if(($x['id']??'')===$id) return $i; } return -1; }
function item_by_id($data,$section,$id){ foreach(($data[$section]??[]) as $x){ if(($x['id']??'')===$id) return $x; } return []; }
function upload_file($field){
    if(empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
    if($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return '';
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
    $type = mime_content_type($_FILES[$field]['tmp_name']);
    if(!isset($allowed[$type])) return '';
    if(!is_dir(MC_UPLOAD_DIR)) mkdir(MC_UPLOAD_DIR, 0775, true);
    $name = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$type];
    $dest = MC_UPLOAD_DIR . '/' . $name;
    if(move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) return MC_UPLOAD_URL . '/' . $name;
    return '';
}
function login_ready($data){ return !empty($data['settings']['admin_hash']); }

$data = load_site();
$flash = '';

if(isset($_GET['logout'])){ session_destroy(); header('Location: admin.php'); exit; }

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'setup_admin'){
    $user = trim($_POST['username'] ?? 'admin');
    $pass = trim($_POST['password'] ?? '');
    if(strlen($pass) >= 6){
        $data['settings']['admin_user'] = $user ?: 'admin';
        $data['settings']['admin_hash'] = password_hash($pass, PASSWORD_DEFAULT);
        save_site($data);
        $_SESSION['mc_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $flash = 'Password must be at least 6 characters.';
}

if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '') === 'login'){
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $adminUser = $data['settings']['admin_user'] ?? 'admin';
    $adminHash = $data['settings']['admin_hash'] ?? '';
    if($user === $adminUser && $adminHash && password_verify($pass, $adminHash)){
        $_SESSION['mc_admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $flash = 'Wrong username or password.';
}

if(empty($_SESSION['mc_admin'])):
$setup = !login_ready($data);
?>
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MasterCare Admin</title><link rel="stylesheet" href="assets/style.css?v=2"></head><body class="admin-login"><form class="login-card" method="post"><div class="logo-mark">MC</div><h1><?= $setup ? 'Create Admin Account' : 'Admin Login' ?></h1><?php if($flash):?><div class="notice"><?= e($flash) ?></div><?php endif;?><input type="hidden" name="action" value="<?= $setup ? 'setup_admin' : 'login' ?>"><input name="username" placeholder="Username" value="admin" required><input name="password" type="password" placeholder="<?= $setup ? 'Create password' : 'Password' ?>" required><button class="btn"><?= $setup ? 'Save Admin' : 'Login' ?></button><a href="index.php">Back to website</a></form></body></html>
<?php exit; endif;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $action = $_POST['action'] ?? '';

    if($action === 'save_settings'){
        foreach(['clinic_name','clinic_name_kh','slogan','slogan_kh','hero_title','hero_title_kh','hero_subtitle','hero_subtitle_kh','phone','address','address_kh','hours','hours_kh','facebook','messenger','telegram','map_url','seo_title','seo_title_kh','seo_description','seo_description_kh'] as $key){
            $data['settings'][$key] = trim($_POST[$key] ?? '');
        }
        foreach(['logo','hero_image'] as $field){
            $uploaded = upload_file($field . '_file');
            $data['settings'][$field] = $uploaded ?: trim($_POST[$field] ?? '');
        }
        save_site($data);
        $flash='Website information saved.';
    }

    if($action === 'change_password'){
        $pass = trim($_POST['new_password'] ?? '');
        if(strlen($pass) >= 6){
            $data['settings']['admin_user'] = trim($_POST['admin_user'] ?? 'admin') ?: 'admin';
            $data['settings']['admin_hash'] = password_hash($pass, PASSWORD_DEFAULT);
            save_site($data);
            $flash='Admin login updated.';
        } else {
            $flash='Password must be at least 6 characters.';
        }
    }

    if(in_array($action, ['save_service','save_promotion','save_product','save_gallery'], true)){
        $map = ['save_service'=>['services','svc'], 'save_promotion'=>['promotions','pro'], 'save_product'=>['products','prd'], 'save_gallery'=>['gallery','gal']];
        [$section,$prefix] = $map[$action];
        $id = trim($_POST['id'] ?? '') ?: new_id($prefix);
        $idx = find_index($data[$section] ?? [], $id);

        if($section === 'services'){
            $image = upload_file('image_file') ?: trim($_POST['image'] ?? '');
            $item = ['id'=>$id,'name'=>trim($_POST['name']??''),'name_kh'=>trim($_POST['name_kh']??''),'category'=>trim($_POST['category']??''),'category_kh'=>trim($_POST['category_kh']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'description_kh'=>trim($_POST['description_kh']??''),'image'=>$image,'active'=>!empty($_POST['active'])];
        } elseif($section === 'promotions'){
            $image = upload_file('image_file') ?: trim($_POST['image'] ?? '');
            $item = ['id'=>$id,'title'=>trim($_POST['title']??''),'title_kh'=>trim($_POST['title_kh']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'description_kh'=>trim($_POST['description_kh']??''),'image'=>$image,'active'=>!empty($_POST['active'])];
        } elseif($section === 'products'){
            $image = upload_file('image_file') ?: trim($_POST['image'] ?? '');
            $item = ['id'=>$id,'name'=>trim($_POST['name']??''),'name_kh'=>trim($_POST['name_kh']??''),'category'=>trim($_POST['category']??''),'category_kh'=>trim($_POST['category_kh']??''),'price'=>trim($_POST['price']??''),'description'=>trim($_POST['description']??''),'description_kh'=>trim($_POST['description_kh']??''),'image'=>$image,'active'=>!empty($_POST['active'])];
        } else {
            $before = upload_file('before_file') ?: trim($_POST['before'] ?? '');
            $after = upload_file('after_file') ?: trim($_POST['after'] ?? '');
            $item = ['id'=>$id,'title'=>trim($_POST['title']??''),'title_kh'=>trim($_POST['title_kh']??''),'service'=>trim($_POST['service']??''),'before'=>$before,'after'=>$after,'note'=>trim($_POST['note']??''),'note_kh'=>trim($_POST['note_kh']??''),'active'=>!empty($_POST['active'])];
        }

        if($idx >= 0) $data[$section][$idx] = $item; else $data[$section][] = $item;
        save_site($data);
        header('Location: admin.php#'.$section);
        exit;
    }

    if($action === 'delete_item'){
        $section = $_POST['section'] ?? '';
        $id = $_POST['id'] ?? '';
        if(isset($data[$section]) && is_array($data[$section])){
            $data[$section] = array_values(array_filter($data[$section], fn($x)=>($x['id']??'') !== $id));
            save_site($data);
            $flash='Item deleted.';
        }
    }

    if($action === 'booking_status'){
        $id = $_POST['id'] ?? '';
        $status = trim($_POST['status'] ?? 'New');
        $idx = find_index($data['bookings'] ?? [], $id);
        if($idx >= 0){ $data['bookings'][$idx]['status'] = $status; save_site($data); $flash='Booking updated.'; }
    }
}
$s = $data['settings'] ?? [];
?>
<!doctype html>
<html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MasterCare Admin Panel</title><link rel="stylesheet" href="assets/style.css?v=2"></head>
<body class="admin-body"><aside><div class="logo-mark">MC</div><h2>MasterCare Admin v2</h2><a href="#dashboard">Dashboard</a><a href="#settings">Website Info</a><a href="#services">Services</a><a href="#promotions">Promotions</a><a href="#gallery">Before/After</a><a href="#products">Products</a><a href="#bookings">Bookings</a><a href="#security">Security</a><a href="index.php" target="_blank">View Website</a><a href="admin.php?logout=1">Logout</a></aside>
<main class="admin-main">
<section id="dashboard" class="admin-panel"><h1>Dashboard</h1><?php if($flash):?><div class="notice"><?= e($flash) ?></div><?php endif;?><div class="grid stats"><div><b><?= count($data['services']??[]) ?></b><span>Services</span></div><div><b><?= count($data['promotions']??[]) ?></b><span>Promotions</span></div><div><b><?= count($data['gallery']??[]) ?></b><span>Results</span></div><div><b><?= count($data['bookings']??[]) ?></b><span>Bookings</span></div></div></section>

<section id="settings" class="admin-panel"><h2>Website Information</h2><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="action" value="save_settings">
<?php foreach(['clinic_name','clinic_name_kh','slogan','slogan_kh','hero_title','hero_title_kh','hero_subtitle','hero_subtitle_kh','phone','address','address_kh','hours','hours_kh','facebook','messenger','telegram','map_url','seo_title','seo_title_kh','seo_description','seo_description_kh'] as $key):?>
<label><?= e(str_replace('_',' ',ucwords($key,'_'))) ?><input name="<?= e($key) ?>" value="<?= e($s[$key] ?? '') ?>"></label>
<?php endforeach;?>
<label>Logo path<input name="logo" value="<?= e($s['logo'] ?? '') ?>"><input type="file" name="logo_file" accept="image/*"></label>
<label>Hero image path<input name="hero_image" value="<?= e($s['hero_image'] ?? '') ?>"><input type="file" name="hero_image_file" accept="image/*"></label>
<button class="btn">Save Website Info</button></form></section>

<?php
function text_input($name,$value=''){ echo '<label>'.e(ucwords(str_replace('_',' ',$name))).'<input name="'.e($name).'" value="'.e($value).'"></label>'; }
function text_area($name,$value=''){ echo '<label>'.e(ucwords(str_replace('_',' ',$name))).'<textarea name="'.e($name).'">'.e($value).'</textarea></label>'; }
$editSection = $_GET['edit_section'] ?? '';
$editId = $_GET['edit_id'] ?? '';
?>

<section id="services" class="admin-panel"><h2>Services</h2><?php $it = $editSection==='services' ? item_by_id($data,'services',$editId) : []; ?><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="action" value="save_service"><input type="hidden" name="id" value="<?= e($it['id']??'') ?>"><?php text_input('name',$it['name']??''); text_input('name_kh',$it['name_kh']??''); text_input('category',$it['category']??''); text_input('category_kh',$it['category_kh']??''); text_input('price',$it['price']??''); text_input('image',$it['image']??''); ?><label>Upload Image<input type="file" name="image_file" accept="image/*"></label><?php text_area('description',$it['description']??''); text_area('description_kh',$it['description_kh']??''); ?><label class="check"><input type="checkbox" name="active" <?= (!isset($it['active'])||$it['active'])?'checked':'' ?>> Active</label><button class="btn"><?= $it?'Update Service':'Add Service' ?></button></form><?php render_table('services',$data,['name','category','price']); ?></section>

<section id="promotions" class="admin-panel"><h2>Promotions</h2><?php $it = $editSection==='promotions' ? item_by_id($data,'promotions',$editId) : []; ?><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="action" value="save_promotion"><input type="hidden" name="id" value="<?= e($it['id']??'') ?>"><?php text_input('title',$it['title']??''); text_input('title_kh',$it['title_kh']??''); text_input('price',$it['price']??''); text_input('image',$it['image']??''); ?><label>Upload Poster<input type="file" name="image_file" accept="image/*"></label><?php text_area('description',$it['description']??''); text_area('description_kh',$it['description_kh']??''); ?><label class="check"><input type="checkbox" name="active" <?= (!isset($it['active'])||$it['active'])?'checked':'' ?>> Active</label><button class="btn"><?= $it?'Update Promotion':'Add Promotion' ?></button></form><?php render_table('promotions',$data,['title','price']); ?></section>

<section id="gallery" class="admin-panel"><h2>Before / After Gallery</h2><?php $it = $editSection==='gallery' ? item_by_id($data,'gallery',$editId) : []; ?><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="action" value="save_gallery"><input type="hidden" name="id" value="<?= e($it['id']??'') ?>"><?php text_input('title',$it['title']??''); text_input('title_kh',$it['title_kh']??''); text_input('service',$it['service']??''); text_input('before',$it['before']??''); ?><label>Upload Before<input type="file" name="before_file" accept="image/*"></label><?php text_input('after',$it['after']??''); ?><label>Upload After<input type="file" name="after_file" accept="image/*"></label><?php text_area('note',$it['note']??''); text_area('note_kh',$it['note_kh']??''); ?><label class="check"><input type="checkbox" name="active" <?= (!isset($it['active'])||$it['active'])?'checked':'' ?>> Active</label><button class="btn"><?= $it?'Update Gallery':'Add Gallery Item' ?></button></form><?php render_table('gallery',$data,['title','service','note']); ?></section>

<section id="products" class="admin-panel"><h2>Products</h2><?php $it = $editSection==='products' ? item_by_id($data,'products',$editId) : []; ?><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="action" value="save_product"><input type="hidden" name="id" value="<?= e($it['id']??'') ?>"><?php text_input('name',$it['name']??''); text_input('name_kh',$it['name_kh']??''); text_input('category',$it['category']??''); text_input('category_kh',$it['category_kh']??''); text_input('price',$it['price']??''); text_input('image',$it['image']??''); ?><label>Upload Image<input type="file" name="image_file" accept="image/*"></label><?php text_area('description',$it['description']??''); text_area('description_kh',$it['description_kh']??''); ?><label class="check"><input type="checkbox" name="active" <?= (!isset($it['active'])||$it['active'])?'checked':'' ?>> Active</label><button class="btn"><?= $it?'Update Product':'Add Product' ?></button></form><?php render_table('products',$data,['name','category','price']); ?></section>

<section id="bookings" class="admin-panel"><h2>Booking Requests</h2><div class="table-wrap"><table><tr><th>Date</th><th>Name</th><th>Phone</th><th>Service</th><th>Message</th><th>Status</th><th>Action</th></tr><?php foreach(array_reverse($data['bookings']??[]) as $b):?><tr><td><?= e($b['created_at']??'') ?></td><td><?= e($b['name']??'') ?></td><td><?= e($b['phone']??'') ?></td><td><?= e($b['service']??'') ?></td><td><?= e($b['message']??'') ?></td><td><?= e($b['status']??'') ?></td><td><form method="post" class="inline-form"><input type="hidden" name="action" value="booking_status"><input type="hidden" name="id" value="<?= e($b['id']??'') ?>"><select name="status"><option>New</option><option>Contacted</option><option>Completed</option><option>Cancelled</option></select><button class="small-btn">Update</button></form></td></tr><?php endforeach;?></table></div></section>

<section id="security" class="admin-panel"><h2>Security</h2><form method="post" class="admin-form"><input type="hidden" name="action" value="change_password"><label>Admin username<input name="admin_user" value="<?= e($s['admin_user'] ?? 'admin') ?>"></label><label>New password<input type="password" name="new_password" placeholder="minimum 6 characters"></label><button class="btn">Change Login</button></form></section>

</main></body></html>
<?php
function render_table($section,$data,$cols){
    echo '<div class="table-wrap"><table><tr>';
    foreach($cols as $c) echo '<th>'.e($c).'</th>';
    echo '<th>Status</th><th>Action</th></tr>';
    foreach(($data[$section]??[]) as $row){
        echo '<tr>';
        foreach($cols as $c) echo '<td>'.e($row[$c]??'').'</td>';
        echo '<td>'.(!isset($row['active'])||$row['active']?'Active':'Hidden').'</td><td class="actions"><a class="small-btn" href="admin.php?edit_section='.e($section).'&edit_id='.e($row['id']??'').'#'.e($section).'">Edit</a><form method="post" onsubmit="return confirm(\'Delete item?\')"><input type="hidden" name="action" value="delete_item"><input type="hidden" name="section" value="'.e($section).'"><input type="hidden" name="id" value="'.e($row['id']??'').'"><button class="small-btn danger">Delete</button></form></td></tr>';
    }
    echo '</table></div>';
}
?>
