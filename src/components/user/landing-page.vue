<template>
    <div class="main-container">
        <div class="container">
            <h1 class="title">
                Welcome to the Evaluation Page
            </h1>
            <br>

            <p class="text-white">
                Please choose your role
            </p>
            <br>

            <div class="button-container">
                <button class="btn" @click.prevent="$router.push('/eval')">
                    Evaluate
                </button>
                <button class="btn" @click.prevent="$router.push('/admin')">
                    Admin
                </button>
            </div>
            
            <p class="text-white"> 
                By continuing you are agreeing to the<a href="#" class="link" @click.prevent="$router.push('/terms')">Terms and Conditions</a>
            </p>
        </div>
    </div>
</template>

<script>
export default {
    name: "landingPage",
    data() {
        return {

        };
    },
};

</script>

<style scoped>

* {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-sizing: border-box;
}

html, body{
    margin: 0;
    padding: 0;
}

.main-container {
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    box-sizing: border-box;
    margin-top: 250px;
}

.text-white, .link {
    color: white;
    text-decoration: none;
    padding: 10px;
}

.title {
    color: white;
}

.container {
    color: white;
    background-color: #232831;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    border-radius: 15px;
    box-sizing: border-box;
    padding: 30px;
}

.button-container {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
}

.btn{
    margin-top: 10px;
    padding: 10px 10px;
    background: #ffe082;
    color: #001821;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn:hover {
    background: #ffd448;
    color: #001821;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    display: flex;
}

@media (max-width: 600px) {
  .button-container {
    flex-direction: column;
  }
}
</style>