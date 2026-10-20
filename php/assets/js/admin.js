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

  // Inline images are uploaded to the server instead of being embedded as base64.
  function uploadImage() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/jpeg,image/png,image/webp,image/gif';
    input.onchange = async () => {
      const file = input.files[0];
      if (!file) return;
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
  imageInput.addEventListener('change', () => {
    const file = imageInput.files[0];
    if (!file) return;
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
  });
})();
