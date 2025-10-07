<?php
// rodnecislo.php
// jednoduchý skript: zadanie rodného čísla -> výpis dátumu narodenia a pohlavia

function sanitize_rnc($input) {
    // odstráni všetko okrem číslic
    return preg_replace('/\D/', '', trim($input));
}

function parse_rodne_cislo($rnc_clean) {
    $len = strlen($rnc_clean);
    if ($len !== 9 && $len !== 10) {
        return ['error' => "Rodné číslo musí mať 9 alebo 10 číslic (po odstránení lomky)."];
    }

    // vezmi prvých 6 znakov: YY MM DD
    $yy = intval(substr($rnc_clean, 0, 2));
    $mm = intval(substr($rnc_clean, 2, 2));
    $dd = intval(substr($rnc_clean, 4, 2));

    $gender = 'muž';

    // bežné: ak mm > 50 => žena (odpočítať 50)
    if ($mm > 50 && ($mm - 50) >= 1 && ($mm - 50) <= 12) {
        $mm = $mm - 50;
        $gender = 'žena';
    } elseif ($mm > 20 && ($mm - 20) >= 1 && ($mm - 20) <= 12) {
        // niektoré špeciálne kódy používajú +20 — to neznamená zmenu pohlavia
        $mm = $mm - 20;
    }

    // rozhodni storočie: preferuj 2000+YY ak nie je v budúcnosti
    $year_candidate_2000 = 2000 + $yy;
    $now_year = intval(date('Y'));
    if ($year_candidate_2000 <= $now_year) {
        $year = $year_candidate_2000;
    } else {
        $year = 1900 + $yy;
    }

    // validácia dátumu
    if (!checkdate($mm, $dd, $year)) {
        return ['error' => "Neplatné rodné číslo / dátum (vypočítané: $dd.$mm.$year)."];
    }

    $date_str = sprintf('%02d.%02d.%04d', $dd, $mm, $year);

    return [
        'date' => $date_str,
        'gender' => $gender,
        'year' => $year,
        'month' => $mm,
        'day' => $dd
    ];
}

// spracovanie POST (formular)
$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = isset($_POST['rc']) ? $_POST['rc'] : '';
    $clean = sanitize_rnc($input);
    $result = parse_rodne_cislo($clean);
}
?>

<!doctype html>
<html lang="sk">
<head>
  <meta charset="utf-8">
  <title>Rozbor rodného čísla</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; padding: 20px; max-width: 700px; margin: auto; }
    label { display:block; margin-bottom:8px; font-weight:bold; }
    input[type="text"] { padding:8px; width:100%; max-width:300px; }
    button { padding:8px 12px; margin-top:8px; }
    .result { margin-top:20px; padding:12px; border-radius:6px; background:#f2f2f2; }
    .error { color: #900; font-weight:bold; }
  </style>
</head>
<body>
  <h2>Urči dátum narodenia z rodného čísla</h2>

  <form method="post" action="">
    <label for="rc">Zadaj rodné číslo (napr. 850412/1234 alebo 8504121234):</label>
    <input id="rc" name="rc" type="text" placeholder="YYMMDD/XXXX" required
           value="<?php echo isset($_POST['rc']) ? htmlspecialchars($_POST['rc']) : ''; ?>">
    <br>
    <button type="submit">Zisti dátum narodenia</button>
  </form>

  <?php if ($result): ?>
    <div class="result">
      <?php if (isset($result['error'])): ?>
        <div class="error"><?php echo htmlspecialchars($result['error']); ?></div>
      <?php else: ?>
        <div><strong>Dátum narodenia:</strong> <?php echo htmlspecialchars($result['date']); ?></div>
        <div><strong>Pohlavie (z RČ):</strong> <?php echo htmlspecialchars($result['gender']); ?></div>
        <div style="margin-top:8px; font-size:90%; color:#555;">
          (Vypočítané z: rok=<?php echo $result['year']; ?>, mesiac=<?php echo $result['month']; ?>, deň=<?php echo $result['day']; ?>)
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <hr>
  <small>Upozornenie: Tento skript len ukazuje základné pravidlá pre čítanie rodného čísla; v niektorých špeciálnych prípadoch (história vydávania čísel, zahraničné čísla, kontrolné cifry) môže byť potrebná ďalšia validácia.</small>
</body>
</html>
