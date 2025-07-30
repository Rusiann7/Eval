<template>
  <nav>
    <ul class="sidebar" ref="sidebar">
      <li @click="hideSidebar">
        <a href="#">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="26"
            viewBox="0 -960 960 960"
            width="26"
            fill="#e3e3e3"
          >
            <path
              d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"
            />
          </svg>
        </a>
      </li>
      <li>
        <a href="#" @click.prevent="$router.push('/')"
          >Testing Phase Version Control 0.3</a
        >
      </li>
      <li>
        <a href="#" @click.prevent="$router.push('/price')">Evaluation</a>
      </li>
    </ul>

    <ul>
      <li>
        <a href="#" @click.prevent="$router.push('/')"
          >Testing Phase Version Control 0.3</a
        >
      </li>
      <li class="hideMobile">
        <a href="#" @click.prevent="$router.push('/')">Home Page</a>
      </li>
      <li class="hideMobile">
        <a href="#" @click.prevent="$router.push('/eval')">Evaluation</a>
      </li>
      <li class="menu-btn" @click="showSidebar">
        <a href="#">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            height="26"
            viewBox="0 -960 960 960"
            width="26"
            fill="#e3e3e3"
          >
            <path
              d="M120-240v-80h720v80H120Zm0-200v-80h720v80H120Zm0-200v-80h720v80H120Z"
            />
          </svg>
        </a>
      </li>
    </ul>
  </nav>

  <div v-if="isloading" class="loading-screen">
    <div class="loading-spinner"></div>
    <p>Loading...</p>
  </div>

  <div class="main-content">
    <div class="content">
      <div v-if="!feedbackSubmitted" class="feedback-form">
        <H2 class="title">EVALUATE</H2>
        <p class="text-white">We value your feedback</p>
        <br />
        <p class="text-white">Select who will you be evaluating</p>

        <div class="tab-container">
          <div
            class="tab"
            :class="{ active: category === 'teacher' }"
            @click="selectCategory('teacher')"
          >
            Teachers
          </div>
          <div
            class="tab"
            :class="{ active: category === 'student' }"
            @click="selectCategory('student')"
          >
            Students
          </div>
        </div>

        <select name="" class="btn" required>
          <option value="Hello">Hello</option>
        </select>

        <p class="text-white">Choose</p>
        <div class="radio-btn">
          <div class="radio-option">
            <input type="radio" id="one" name="rating" value="1" v-model="rating" />
            <label for="one">1</label>
          </div>

          <div class="radio-option">
            <input type="radio" id="two" name="rating" value="2" v-model="rating"/>
            <label for="two">2</label>
          </div>

          <div class="radio-option">
            <input type="radio" id="three" name="rating" value="3" v-model="rating" />
            <label for="three">3</label>
          </div>

          <div class="radio-option">
            <input type="radio" id="four" name="rating" value="4" v-model="rating"/>
            <label for="four">4</label>
          </div>
        </div>

        <p class="text-white">Enter comment</p>
        <textarea
          id="feedback"
          v-model="feedback"
          class="feedback-input"
          placeholder="Input Text Here"
          required
        >
        </textarea>

        <button type="submit" class="btn" @click.prevent="submitFeedback">
          Submit
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "evalForm",
  data() {
    return {
      FeedbackData: {
        feedback: "",
        rating: 0,
      },
      feedbackSubmitted: false,
      responseMessage: null,
      feedback: "",
      rating: 0,
      urlappphp: "",
      isloading: false,
      category: "teachers",
    };
  },

  methods: {
    async selectCategory(category) {
      this.category = category;
    },

    async submitFeedback() {
      this.isloading = true;

      const urls = [
        "https://star-panda-literally.ngrok-free.app/feedback.php",
        "https://rusiann7.helioho.st/feedback.php",
        "http://localhost:8080/feedback.php"
      ];

      for (const url of urls) {
        try {
          const testResponse = await fetch(url, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action: "ping"})
          });

          if (testResponse.ok) {
            this.urlappphp = url;
            break;
          }
        }catch (_) {
          continue;
        }
      }

      try {
        if (this.rating < 1 || this.rating > 5) {
          alert("Error");
          return;
        }

        const response = await fetch(this.urlappphp, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            action: "feedback",
            feedback: this.feedback,
            rating: this.rating,
            category: this.category,
          }),
        });

        const result = await response.json();

        if(result.success) {
          this.feedbackSubmitted = true;
          alert("Feedback has been submitted successfully!");
        }else {
          alert (result.error || "Error")
        }

      }catch (error) {
        console.error("Error submitting feedback:", error)
      }finally {
        this.isloading = false;
      }
    },

    showSidebar() {
      this.$refs.sidebar.style.display = "flex";
    },

    hideSidebar() {
      this.$refs.sidebar.style.display = "none";
    },
  },
};
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  box-sizing: border-box;
}

html,
body {
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

nav {
  background-color: #2d333f;
  box-shadow: 3px 3px 5px rgba(0, 0, 0, 0.1);
  position: fixed;
  top: 0;
  z-index: 100;
  margin: 0;
  padding: 0;
  width: 100%;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

nav ul {
  width: 100%;
  list-style: none;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

nav li {
  height: 50px;
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

nav a {
  height: 100%;
  padding: 0 30px;
  text-decoration: none;
  display: flex;
  align-items: center;
  color: #e3e3e3;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  font-weight: 500;
  font-size: 15px;
  letter-spacing: 0.5px;
}

nav li:first-child a {
  font-size: 18px;
  font-weight: 600;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

nav a:hover {
  background-color: #3a4252;
  color: white;
}

nav a:active {
  background-color: #4a5568; /* pressed state */
}

nav li:first-child {
  margin-right: auto;
}

.sidebar {
  position: fixed;
  top: 0;
  right: 0;
  height: 100vh;
  width: 250px;
  z-index: 999;
  background-color: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  box-shadow: -10px 0 10px rgba(0, 0, 0, 0.1);
  display: none;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
  margin: 0;
  padding: 0;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar li {
  width: 100%;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar a {
  width: 100%;
  font-size: 16px;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.sidebar a:hover {
  background-color: #3a4252;
}

.menu-btn {
  display: none;
}

.menu-btn:hover {
  background-color: #3a4252;
}

@media (max-width: 800px) {
  .hideMobile {
    display: none;
  }
  .menu-btn {
    display: block;
  }
}

@media (max-width: 400px) {
  .sidebar {
    width: 100%;
  }
}

.main-content {
  display: flex;
  z-index: 99999;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
  margin-top: 250px;
}

.content {
  color: white;
  background-color: #232831;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  box-sizing: border-box;
  z-index: 9999;
  border-radius: 15px;
  padding: 30px;
  position: relative;
}

.radio-btn {
  display: flex;
  gap: 20px;
  margin: 20px 0;
  width: 100%;
  justify-content: center;
  align-items: center;
  margin-bottom: 20px;
}

.radio-option {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.radio-option input[type="radio"] {
  margin-bottom: 8px;
  accent-color: #4a5568;
  width: 25px;
  height: 18px;
}

.radio-option label {
  color: white;
  font-size: 14px;
  cursor: pointer;
}

.tab-container {
  display: flex;
  align-items: center; /* vertical alignment */
  justify-content: space-between; /* put title left, button right */
  margin-bottom: 24px;

  padding: 8px 0;
  color: #ffffff;
}

.tab {
  padding: 0.5rem 1rem;
  background: #ffe082;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  color: #001821;
  transition: all 0.3s ease;
}

.tab.active {
  background: #333;
  color: #fff;
}

.tab:hover {
  color: #001821;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  display: flex;
}

.tab.active:hover {
  color: white;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  display: flex;
}

.feedback-input {
  display: block;
  width: 100%;
  height: 100px;
  margin-top: 10px;
  padding: 10px;
  font-size: 1rem;
  border: 1px solid #ccc;
  border-radius: 5px;
  background: #fff;
  color: #000;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.btn {
  margin-top: 10px;
  padding: 10px 20px;
  background: #ffe082;
  color: #001821;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.btn:hover {
  background: #ffd448;
  color: #001821;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.loading-screen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  color: white;
}

.loading-spinner {
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top: 4px solid #ffffff;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}
</style>
