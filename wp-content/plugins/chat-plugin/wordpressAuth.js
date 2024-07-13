async function getWordPressUser(cookies) {
    try {
      const fetch = await import('node-fetch');
      const response = await fetch.default('https://juchheim.local/wp-json/wp/v2/users/me', {
        headers: {
          'Cookie': cookies,
        },
      });
  
      if (!response.ok) {
        throw new Error('Failed to authenticate user');
      }
  
      const user = await response.json();
      console.log('User fetched:', user);
      return {
        id: user.id,
        username: user.name,
      };
    } catch (error) {
      console.error('Error fetching WordPress user:', error);
      return null;
    }
  }
  
  module.exports = { getWordPressUser };
  