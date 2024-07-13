const fetch = require('node-fetch');

async function getWordPressUser(cookies) {
  // Implement the logic to get the WordPress user based on cookies
  // Example:
  try {
    const response = await fetch('https://juchheim.online/wp-json/wp/v2/users/me', {
      headers: {
        'Content-Type': 'application/json',
        'Cookie': cookies,
      },
      credentials: 'include'
    });
    if (response.ok) {
      const user = await response.json();
      return user;
    } else {
      return null;
    }
  } catch (error) {
    console.error('Error fetching WordPress user:', error);
    return null;
  }
}

module.exports = { getWordPressUser };
