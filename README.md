# Shared Games
An API for Board Game Atlas

## Getting started
1. Sign up for an API at [Board Game Atlas ( BGA )](https://api.boardgameatlas.com/api/docs/apps)
2. Install and active the plugin.
3. Add your client id from BGA to **Board Games > Settings**
4. Click Fetch API
5. Click Insert Missing BGA Games

## Board Game Atlas Rate Limits
The API allows for up to **60 requests per minute** per client_id. Exceeding this amount will result in an HTTP 429 Too Many Requests status code.

**All data is updated only every 24 hours** so additional requests will not result in new data.


