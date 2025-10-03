<?php
function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

// ---------- cURL Helper Function ----------
function call_api($url, $payload){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "User-Agent: Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Mobile Safari/537.36",
        "Origin: https://wall.offerpro.io",
        "Referer: https://wall.offerpro.io/"
    ]);
    // SSL disable (Render में verify issue avoid करने के लिए)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $resp = curl_exec($ch);
    if(curl_errno($ch)){
        return json_encode(["error" => curl_error($ch)]);
    }
    curl_close($ch);
    return $resp;
}

// ---------- AJAX Claim Real Link ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'claim') {
    header("Content-Type: application/json");
    $input = json_decode(file_get_contents("php://input"), true);

    $payload = [
      "offer" => (int)$input["offer_id"],
      "enc" => $input["enc"],
      "app_id" => (int)$input["app_id"],
      "device_id" => $input["device_id"],
      "source" => $input["source"]
    ];

    echo call_api("https://server.offerpro.io/api/postbacks/claim/", $payload);
    exit;
}

// ---------- AJAX Ongoing Offers ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_GET['action'] ?? '') === 'ongoing') {
    header("Content-Type: application/json");
    $input = json_decode(file_get_contents("php://input"), true);

    $payload = [
      "enc" => $input["enc"],
      "app_id" => (int)$input["app_id"],
      "status" => "ONGOING",
      "device_id" => $input["device_id"]
    ];

    echo call_api("https://server.offerpro.io/api/postbacks/list_postback/", $payload);
    exit;
}

// ---------- Normal Page Load ----------
$response_json = null;
$error = null;
$app_id = null; $enc = null;
// Dummy device id (unique होना चाहिए)
$device_id = "243236bd288b4aacafabe84de1996cd3ce5558e561f0f0d41fe87e57d3f71584";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $input_url = trim($_POST['input_url'] ?? '');

    if ($input_url) {
        $parts = parse_url($input_url);
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $qs);
            if (!empty($qs['app_id'])) $app_id = $qs['app_id'];
            if (!empty($qs['enc'])) $enc = $qs['enc'];
        }
    }

    if (!$app_id || !$enc) {
        $error = "❌ app_id या enc नहीं मिला।";
    } else {
        $payload = [
            "enc" => $enc,
            "app_id" => (int)$app_id,
            "user_country" => "country.country_code",
            "user_ip" => "country.ip",
            "device_id" => $device_id
        ];

        $api_resp = call_api("https://server.offerpro.io/api/tasks/list_tasks/?ordering=-cpc&no_pagination=false&page=1", $payload);
        $response_json = json_decode($api_resp, true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>BYPASS</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding-top: 20px;
      background: linear-gradient(270deg, #ff0080, #7928ca, #2afadf, #00c9ff);
      background-size: 800% 800%;
      animation: gradientBG 7s ease infinite;
      color: #fff;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    .container {
      background: rgba(0, 0, 0, 0.75);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 25px rgba(0, 255, 255, 0.3);
      text-align: center;
      max-width: 600px;
      width: 95%;
    }
    .best-heading {
      font-size: 1.5rem;
      font-weight: 800;
      background: linear-gradient(90deg, #2afadf, #c346c2);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: shimmer 3s infinite linear;
      text-transform: uppercase;
      letter-spacing: 2px;
      margin-bottom: 20px;
    }
    @keyframes shimmer { 0%{background-position:-500%}100%{background-position:500%} }
    .input {
      width: 100%;
      padding: 12px;
      border: 2px solid #00f2ff;
      border-radius: 10px;
      background: #000;
      color: #fff;
      text-align: center;
      font-size: 15px;
      margin-bottom: 15px;
    }
    .btn {
      background: #00c9ff;
      color: black;
      padding: 12px 25px;
      border: none;
      border-radius: 10px;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn:hover { background: #2afadf; }
    .section { margin-top: 25px; text-align:left }
    .section h3 { text-align:center; margin-bottom:15px; color:#2afadf }
    .offer {
      display: flex;
      align-items: center;
      gap: 12px;
      background: rgba(255,255,255,0.05);
      padding: 12px;
      border-radius: 10px;
      margin: 12px 0;
    }
    .offer img { width: 50px; height: 50px; border-radius: 6px; }
    .meta { flex: 1; text-align: left; }
    .name { font-weight: bold; }
    .coins { color: #2afadf; }
    .copybtn {
      background: #00c9ff;
      border: none;
      padding: 6px 12px;
      border-radius: 8px;
      cursor: pointer;
    }
    .copybtn.copied { background:#2afadf; }
  </style>
</head>
<body>
<div class="container">
  <h2 class="best-heading">OfferPro Panel</h2>

  <form method="post">
    <input class="input" type="text" name="input_url" placeholder="Enter OfferPro URL" value="<?=h($_POST['input_url'] ?? '')?>" required>
    <button class="btn" type="submit">Fetch Offers</button>
  </form>

  <?php if($error): ?>
    <p style="color:#ff4d6d;"><?=h($error)?></p>
  <?php endif; ?>

  <?php if($response_json && isset($response_json['results'])): ?>
    <div class="section">
      <h3>🆕 New Offers</h3>
      <?php foreach($response_json['results'] as $offer): ?>
        <div class="offer">
          <img src="<?=h($offer['offer_image'])?>">
          <div class="meta">
            <div class="name"><?=h($offer['name'])?></div>
            <div class="coins">Coins: <?=h($offer['reward_coins'])?></div>
          </div>
          <button class="copybtn" onclick="claimOffer(<?=h($offer['id'])?>,'<?=h($offer['source'])?>',this)">Get Link</button>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="section" style="text-align:center;">
      <button class="btn" type="button" onclick="loadOngoing()">⏳ Show Ongoing Offers</button>
    </div>
    <div id="ongoingOffers"></div>
  <?php endif; ?>
</div>

<script>
function claimOffer(offerId, source, btn){
  fetch("?action=claim", {
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body: JSON.stringify({
      offer_id: offerId,
      source: source,
      enc: "<?=h($enc??'')?>",
      app_id: "<?=h($app_id??'')?>",
      device_id: "<?=$device_id?>"
    })
  })
  .then(r=>r.json())
  .then(data=>{
    if(data.offer_link){
      navigator.clipboard.writeText(data.offer_link);
      btn.innerText="✔ Copied!";
      btn.classList.add("copied");
      setTimeout(()=>{btn.innerText="Get Link";btn.classList.remove("copied")},2000);
    } else {
      alert("❌ Claim API Response: "+JSON.stringify(data));
    }
  })
  .catch(err=>alert("❌ Request error: "+err));
}

function loadOngoing(){
  fetch("?action=ongoing", {
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body: JSON.stringify({
      enc: "<?=h($enc??'')?>",
      app_id: "<?=h($app_id??'')?>",
      device_id: "<?=$device_id?>"
    })
  })
  .then(r=>r.json())
  .then(data=>{
    let div = document.getElementById("ongoingOffers");
    div.innerHTML = "<h3>⏳ Ongoing Offers</h3>";

    let offers = Array.isArray(data) ? data : [];
    if(offers.length){
      offers.forEach(offer=>{
        div.innerHTML += `
          <div class="offer">
            <img src="${offer.offer_image||''}">
            <div class="meta">
              <div class="name">${offer.offer_name||'Unknown'}</div>
              <div class="coins">Coins: ${offer.reward_currency||0}</div>
            </div>
            <button class="copybtn" onclick="copyOngoing('${offer.offer_link||''}',this)">Get Link</button>
          </div>
        `;
      });
    } else {
      div.innerHTML += "<p>No ongoing offers found.</p>";
    }
  })
  .catch(err=>alert("❌ Ongoing API error: "+err));
}

function copyOngoing(link, btn){
  if(link){
    navigator.clipboard.writeText(link);
    btn.innerText="✔ Copied!";
    btn.classList.add("copied");
    setTimeout(()=>{btn.innerText="Get Link";btn.classList.remove("copied")},2000);
  } else {
    alert("❌ No link found in ongoing offer");
  }
}
</script>
</body>
</html>