(function () {
  const form = document.getElementById('article-form');
  if (!form) return;
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  const quill = new Quill('#editor', {
    theme: 'snow',
    placeholder: 'Мэдээний агуулгаа энд бичнэ үү...',
    modules: {
      toolbar: {
        container: [
          [{ header: [2, 3, false] }],
          ['bold', 'italic', 'underline', 'strike'],
          [{ list: 'ordered' }, { list: 'bullet' }],
          ['blockquote', 'link', 'image', 'video'],
          [{ align: [] }],
          ['clean'],
        ],
        handlers: { image: uploadImage },
      },
    },
  });

  // Phone photos are often 5-10MB, above many hosts' upload limit, so large JPEG/PNG/WEBP
  // files are shrunk in the browser before sending. GIFs (may be animated) are left alone.
  const MAX_SIDE = 1600;
  async function shrinkImage(file) {
    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) return file;
    try {
      const bmp = await createImageBitmap(file, { imageOrientation: 'from-image' });
      const scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
      if (scale === 1 && file.size < 1.5 * 1024 * 1024) return file;
      const canvas = document.createElement('canvas');
      canvas.width = Math.round(bmp.width * scale);
      canvas.height = Math.round(bmp.height * scale);
      canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
      const type = file.type === 'image/png' ? 'image/png' : 'image/jpeg';
      const blob = await new Promise((resolve) => canvas.toBlob(resolve, type, 0.85));
      if (!blob || blob.size >= file.size) return file;
      const name = file.name.replace(/\.[^.]+$/, '') + (type === 'image/png' ? '.png' : '.jpg');
      return new File([blob], name, { type, lastModified: Date.now() });
    } catch (e) {
      return file;
    }
  }

  // Inline images are uploaded to the server instead of being embedded as base64.
  function uploadImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.onchange = async () => {
      if (!input.files[0]) return;
      const file = await shrinkImage(input.files[0]);
      const body = new FormData();
      body.append('image', file);
      const res = await fetch(window.UPLOAD_URL, { method: 'POST', body, headers: { 'x-csrf-token': csrf } });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) return alert(data.error || 'Зураг хуулахад алдаа гарлаа.');
      const range = quill.getSelection(true);
      quill.insertEmbed(range.index, 'image', data.url, 'user');
      quill.setSelection(range.index + 1);
    };
    input.click();
  }

  form.addEventListener('submit', () => {
    document.getElementById('content').value = quill.getText().trim() || quill.root.querySelector('img,iframe') ? quill.root.innerHTML : '';
  });

  const imageInput = document.getElementById('image-input');
  const preview = document.getElementById('image-preview');
  const saveButton = form.querySelector('.form-side button[type=submit]');
  imageInput.addEventListener('change', async () => {
    const original = imageInput.files[0];
    if (!original) return;
    preview.src = URL.createObjectURL(original);
    preview.classList.remove('hidden');
    saveButton.disabled = true;
    const file = await shrinkImage(original);
    if (file !== original && window.DataTransfer) {
      const dt = new DataTransfer();
      dt.items.add(file);
      imageInput.files = dt.files;
    }
    saveButton.disabled = false;
  });
})();
