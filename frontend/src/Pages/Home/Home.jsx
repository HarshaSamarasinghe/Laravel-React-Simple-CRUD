import "./Home.css";
import { useNavigate } from "react-router-dom";

const Home = () => {
  const navigate = useNavigate();
  return (
    <div>
      <div className="heroSection">
        <div className="heroLeft"></div>
        <div className="heroRight">
          <div className="heroBg">
           <button onClick={() => navigate("/products")} className="heroMainTitle">Product Store</button>
            
          </div>
          
        </div>
      </div>
      
    </div>
  );
};

export default Home;
