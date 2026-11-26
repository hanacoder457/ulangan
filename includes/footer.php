<footer>
    &copy; <?= date('Y') ?> Website By Nak
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
  document.querySelectorAll('.card.text-white .fs-2.fw-bold').forEach(function (el) {
    var target = parseInt(el.textContent.replace(/\D/g, ''), 10);
    if (isNaN(target) || target === 0) { return; }
    var durasi = 900, mulai = null;
    el.textContent = '0';
    function langkah(waktu) {
      if (mulai === null) { mulai = waktu; }
      var t = Math.min((waktu - mulai) / durasi, 1);
      var ease = 1 - Math.pow(1 - t, 3);
      el.textContent = Math.round(target * ease);
      if (t < 1) { requestAnimationFrame(langkah); }
    }
    setTimeout(function () { requestAnimationFrame(langkah); }, 300);
  });
});
</script>
</body>
</html>
