// import React from "react";
// import { BrowserRouter, Routes, Route } from "react-router-dom";

// import ProductList from "./pages/ProductList";
// import CreateProduct from "./pages/CreateProduct";
// import EditProduct from "./pages/EditProduct";

// export default function App() {
//   return (
//     <BrowserRouter>
//       <Routes>
//         <Route path="/" element={<ProductList />} />
//         <Route path="/create" element={<CreateProduct />} />
//         <Route path="/edit/:id" element={<EditProduct />} />
//       </Routes>
//     </BrowserRouter>
//   );
// }


import "./App.css";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";

import Home from "./Pages/Home/Home";
import ProductList from "./Pages/Product/ProductList";
import CreateProduct from "./Pages/Product/CreateProduct";
import EditProduct from "./Pages/Product/EditProduct";


function App() {
  return (
    <>
      <Router>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/products" element={<ProductList />} />
          <Route path="/create" element={<CreateProduct />} />
          <Route path="/edit/:id" element={<EditProduct />} />
        </Routes>
      </Router>
    </>
  );
}

export default App;
