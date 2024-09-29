function escapeHtml(text) {
  let map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  console.log(text.replace(/[&<>"']/g, function(m) { return map[m]; }));

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
