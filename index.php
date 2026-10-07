<?php
$name = '';
$message = '';
$submitted = false;
// Handle an HTTP 'QUERY' request sent via Fetch (JSON body).
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'QUERY') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true) ?: [];
    $name = isset($data['name']) ? trim($data['name']) : '';
    $message = isset($data['message']) ? trim($data['message']) : '';
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['name' => $name, 'message' => $message]);
    exit;
}

// Fallback: support query parameters (GET) when JavaScript is not used.
if (isset($_GET['submit'])) {
    $name = isset($_GET['name']) ? trim($_GET['name']) : '';
    $message = isset($_GET['message']) ? trim($_GET['message']) : '';
    $submitted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POST Form</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 720px; margin: 24px auto; padding: 0 12px; }
        label { display: block; margin: 8px 0; }
        textarea { width: 100%; height: 120px; }
    </style>
</head>
<body>
<?php if ($submitted): ?>
    <h2>Received</h2>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Message:</strong><br><?php echo nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')); ?></p>
<?php endif; ?>

<h1>Contact Form</h1>
<form method="get" action="" id="contactForm">
    <label>
        Name:
        <input type="text" name="name" value="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>">
    </label>
    <label>
        Message:
        <textarea name="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
    </label>
        <button type="submit" name="submit">Send</button>
</form>

<script>
// Submit the form using the HTTP 'QUERY' method via Fetch API.
// Note: HTML forms don't support 'QUERY'; we intercept submit and send via Fetch.
(function(){
    function escapeHtml(s){ return s ? s.replace(/[&<>\"']/g, function(m){ return {'&':'&amp;','<':'&lt;','>':'&gt;','\\"':'&quot;',"'":'&#39;'}[m]; }) : ''; }

    const form = document.getElementById('contactForm');
    if (!form) return;
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const name = form.elements['name'].value;
        const message = form.elements['message'].value;
        try {
            const res = await fetch(window.location.href, {
                method: 'QUERY',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, message: message })
            });
            if (!res.ok) throw new Error(res.status + ' ' + res.statusText);
            const data = await res.json();
            let out = document.getElementById('received-query');
            if (!out) {
                out = document.createElement('div');
                out.id = 'received-query';
                form.parentNode.insertBefore(out, form);
            }
            out.innerHTML = '<h2>Received (QUERY)</h2>' +
                '<p><strong>Name:</strong> ' + escapeHtml(data.name) + '</p>' +
                '<p><strong>Message:</strong><br>' + escapeHtml(data.message).replace(/\n/g, '<br>') + '</p>';
        } catch (err) {
            alert('Request failed: ' + err.message);
        }
    });
})();
</script>

</body>
</html>