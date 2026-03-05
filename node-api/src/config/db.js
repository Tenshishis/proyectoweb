const mongoose = require('mongoose');

exports.connect = () => {
  const uri = process.env.MONGO_URI;
  return mongoose.connect(uri, {
    useNewUrlParser: true,
    useUnifiedTopology: true
  });
};
