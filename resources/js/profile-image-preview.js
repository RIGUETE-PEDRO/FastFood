document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('foto-upload');
  const preview = document.getElementById('preview-image');

  if (!input || !preview) return;

  input.addEventListener('change', (e) => {
    const file = e.target?.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
      preview.src = event.target?.result || preview.src;
    };
    reader.readAsDataURL(file);
  });
});
