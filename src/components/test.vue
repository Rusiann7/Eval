<template>
  <div>
    <!-- Certificate Content -->
    <div ref="printSection">
      <div style="font-family: Arial, sans-serif; padding: 40px; width: 700px;">
        <h1 style="font-size: 24px; margin-bottom: 20px;">Certificate of Completion</h1>
        <p>
          This certifies that
          <strong>{{ recipient }}</strong>
          has completed the requirements on
          <strong>{{ formattedDate }}</strong>.
        </p>

        <div style="margin-top: 60px;">
          <p>Sincerely,</p>
          <p style="margin-top: 30px; text-decoration: underline;">{{ signatory.name }}</p>
          <p>{{ signatory.position }}</p>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div style="margin-top: 20px; display: flex; gap: 10px;">
      <button @click="printDoc" style="padding: 8px 16px; background: #4a7cff; color: white; border: none; border-radius: 4px;">Print</button>
      <button @click="savePdf" style="padding: 8px 16px; background: #2ecc71; color: white; border: none; border-radius: 4px;">Save PDF</button>
    </div>
  </div>
</template>

<script>
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'

export default {
  name: "TestComponent",
  data() {
    return {
      recipient: 'Juan Dela Cruz',
      date: new Date().toISOString().split('T')[0],
      signatory: {
        name: 'Engr. Maria Santos',
        position: 'Chief Operations Officer'
      }
    }
  },
  computed: {
    formattedDate() {
      return new Date(this.date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
  },
  methods: {
    printDoc() {
      const content = this.$refs.printSection.innerHTML
      const win = window.open('', '', 'height=600,width=800')
      win.document.write(`
        <html>
          <head>
            <title>Print</title>
            <style>
              body { font-family: Arial, sans-serif; padding: 40px; }
              h1 { font-size: 24px; margin-bottom: 20px; }
              .signature { margin-top: 60px; }
              .signatory-name { text-decoration: underline; margin-top: 30px; }
            </style>
          </head>
          <body>${content}</body>
        </html>
      `)
      win.document.close()
      win.focus()
      win.print()
    },
    savePdf() {
  const element = this.$refs.printSection
  html2canvas(element, { scale: 2 }).then((canvas) => {
    const imgData = canvas.toDataURL('image/png')
    const pdf = new jsPDF('p', 'mm', 'a4')
    const pdfWidth = pdf.internal.pageSize.getWidth()
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width
    pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight)
    pdf.save('certificate.pdf')
  })
}
  }
}
</script>
